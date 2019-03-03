<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
	 *
	 * This program is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license    https://www.gnu.org/licenses/gpl.html GPL License
	 * @link       https://twistphp.com
	 */

	namespace Twist\Core\Models\Protect;

	final class Firewall{

		//Specifies if the firewall is running or not
		public static $blEnabled = true;

		//Failed logins before soft ban
		public static $intLoginLimit = 5;

		//Failed password resets before soft ban
		public static $intResetLimit = 3;

		//Time in seconds to soft ban a user
		public static $intInitialBanSeconds = 300;

		//Amount of soft bans before a full ban is put in place
		public static $intMaxSoftBans = 3;

		//Time in seconds for a full ban to last
		public static $intFullBanSeconds = 345600;

		//Reset ban history after X days of last ban
		public static $intResetAfterDays = 30;

		//Reset soft ban limits after successful login
		public static $blResetAfterSuccess = true;

		//Store the twist cache history for X days
		public static $intTwistCacheLife = 30;

		public static $blLoaded = false;
		public static $arrFailedActions = array();
		public static $arrBannedIPs = array();
		public static $arrWhitelistIPs = array();
		public static $arrBanHistory = array();

		/**
		 * Load all the firewall data from the cache
		 */
		protected static function load(){

			if(self::$blLoaded  === false){

				self::$blEnabled = \Twist::framework()->setting('TWISTPROTECT_FIREWALL');
				self::$intLoginLimit = \Twist::framework()->setting('TWISTPROTECT_FIREWALL_LOGIN_LIMIT');
				self::$intResetLimit = \Twist::framework()->setting('TWISTPROTECT_FIREWALL_RESET_LIMIT');
				self::$intInitialBanSeconds = \Twist::framework()->setting('TWISTPROTECT_FIREWALL_INITIAL_BAN');
				self::$intMaxSoftBans = \Twist::framework()->setting('TWISTPROTECT_FIREWALL_FULL_BAN');
				self::$intFullBanSeconds = \Twist::framework()->setting('TWISTPROTECT_FIREWALL_MAX_SOFTBANS');
				self::$intResetAfterDays = \Twist::framework()->setting('TWISTPROTECT_FIREWALL_RESET_AFTER_DAYS');
				self::$blResetAfterSuccess = \Twist::framework()->setting('TWISTPROTECT_FIREWALL_RESET_AFTER_SUCCESS');

				if(self::$blEnabled){
					self::$arrFailedActions = \Twist::Cache()->read('protect/failed-actions');
					self::$arrBannedIPs = \Twist::Cache()->read('protect/banned-ips');
					self::$arrWhitelistIPs = \Twist::Cache()->read('protect/whitelist-ips');
					self::$arrBanHistory = \Twist::Cache()->read('protect/ban-history');
				}

				self::$blLoaded = true;
			}
		}

		/**
		 * Output all the firewall info, current bans and settings that we have
		 * @return array
		 */
		public static function info(){

			self::load();

			return array(
				'settings' => array(
					'enabled' => self::$blEnabled,
					'login_limit' => self::$intLoginLimit,
					'reset_limit' => self::$intResetLimit,
					'soft_ban_hours' => self::$intInitialBanSeconds,
					'max_soft_bans' => self::$intMaxSoftBans,
					'full_ban_hours' => self::$intFullBanSeconds,
					'reset_history_days' => self::$intResetAfterDays,
					'reset_after_success' => self::$blResetAfterSuccess,
				),
				'failed_actions' => self::$arrFailedActions,
				'banned_ips' => self::$arrBannedIPs,
				'whitelist_ips' => self::$arrWhitelistIPs,
				'ban_history' => self::$arrBanHistory
			);
		}

		/**
		 * Run the firewall and deny access to any user that is on the ban list
		 * Banned users will spend time processing the band list and tidying up old records
		 */
		public static function firewall(){

			self::load();

			//Only run the firewall if it is enabled
			if(self::$blEnabled){

				if(array_key_exists($_SERVER['REMOTE_ADDR'],self::$arrBannedIPs) && !array_key_exists($_SERVER['REMOTE_ADDR'],self::$arrWhitelistIPs)){
					//Users that are already banned can process the band list
					self::processBanned();
					\Twist::respond(403);
				}
			}
		}

		/**
		 * Processing the band list and tidying up old records
		 * Wipe old ban histories for IP addresses that have not been banned for X days
		 */
		protected static function processBanned(){

			foreach(self::$arrBannedIPs as $strIPAddress => $arrEachBan){
				if(strtotime($arrEachBan['expire']) <= time()){
					self::unbanIP($strIPAddress);
				}
			}

			foreach(self::$arrBanHistory as $strIPAddress => $arrEachBan){
				if(strtotime($arrEachBan['last_banned']) <= strtotime('-'.self::$intResetAfterDays.' Days')){
					self::resetBanHistory($strIPAddress);
				}
			}
		}

		/**
		 * Upon successful login reset the users soft ban limits
		 */
		public static function successLogin(){

			self::load();

			if(self::$blEnabled){
				$strIPAddress = $_SERVER['REMOTE_ADDR'];

				if(self::$blResetAfterSuccess && array_key_exists($strIPAddress,self::$arrFailedActions)){
					self::$arrFailedActions[$strIPAddress]['failed_logins'] = 0;
					self::$arrFailedActions[$strIPAddress]['password_resets'] = 0;

					\Twist::Cache()->write('protect/failed-actions',self::$arrFailedActions,86400*self::$intTwistCacheLife);
				}
			}
		}

		/**
		 * Log a failed login attempt by an IP address, multiple login attempts without a success will trigger a soft ban
		 */
		public static function failedLogin(){

			self::load();

			if(self::$blEnabled && self::$intLoginLimit > 0){
				$strIPAddress = $_SERVER['REMOTE_ADDR'];

				if(!array_key_exists($strIPAddress,self::$arrWhitelistIPs)){

					if(array_key_exists($strIPAddress,self::$arrFailedActions)){
						self::$arrFailedActions[$strIPAddress]['failed_logins']++;
						self::$arrFailedActions[$strIPAddress]['last_attempt'] = date('Y-m-d H:i:s');
					}else{
						self::$arrFailedActions[$strIPAddress] = array(
							'first_attempt' => date('Y-m-d H:i:s'),
							'last_attempt' => date('Y-m-d H:i:s'),
							'failed_logins' => 1,
							'password_resets' => 0
						);
					}

					if(self::$arrFailedActions[$strIPAddress]['failed_logins'] >= self::$intLoginLimit){
						unset(self::$arrFailedActions[$strIPAddress]);
						self::banIP($strIPAddress,'To many failed logins');
					}

					\Twist::Cache()->write('protect/failed-actions',self::$arrFailedActions,86400*self::$intTwistCacheLife);
				}
			}
		}

		/**
		 * Login a password reset attempt by an IP address, multiple reset attempts without a login will trigger a soft ban
		 */
		public static function passwordReset(){

			self::load();

			if(self::$blEnabled && self::$intResetLimit > 0){
				$strIPAddress = $_SERVER['REMOTE_ADDR'];

				if(!array_key_exists($strIPAddress,self::$arrWhitelistIPs)){

					if(array_key_exists($strIPAddress,self::$arrFailedActions)){
						self::$arrFailedActions[$strIPAddress]['password_resets']++;
						self::$arrFailedActions[$strIPAddress]['last_attempt'] = date('Y-m-d H:i:s');
					}else{
						self::$arrFailedActions[$strIPAddress] = array(
							'first_attempt' => date('Y-m-d H:i:s'),
							'last_attempt' => date('Y-m-d H:i:s'),
							'failed_logins' => 0,
							'password_resets' => 1
						);
					}

					if(self::$arrFailedActions[$strIPAddress]['password_resets'] >= self::$intResetLimit){
						unset(self::$arrFailedActions[$strIPAddress]);
						self::banIP($strIPAddress,'To many password resets');
					}

					\Twist::Cache()->write('protect/failed-actions',self::$arrFailedActions,86400*self::$intTwistCacheLife);
				}
			}
		}

		/**
		 * Ban an IP address from loading pages within the system, soft bans will be auto escalated to a full ban
		 * @param $strIPAddress
		 * @param string $strReason Reason for banning the user
		 * @param bool $blApplyFullBan Escalate a ban to be a full ban by passing true
		 * @return bool
		 */
		public static function banIP($strIPAddress,$strReason = '',$blApplyFullBan = false){

			self::load();

			//Whitelist users cannot be banned
			if(!array_key_exists($strIPAddress,self::$arrWhitelistIPs)){

				self::$arrBannedIPs[$strIPAddress] = array(
					'reason' => $strReason,
					'banned' => date('Y-m-d H:i:s'),
					'length' => self::$intInitialBanSeconds,
					'expire' => date('Y-m-d H:i:s',strtotime('+'.self::$intInitialBanSeconds.' Seconds'))
				);

				if(array_key_exists($strIPAddress,self::$arrBanHistory)){
					self::$arrBanHistory[$strIPAddress]['bans']++;
					self::$arrBanHistory[$strIPAddress]['last_banned'] = date('Y-m-d H:i:s');
				}else{
					self::$arrBanHistory[$strIPAddress] = array(
						'first_banned' => date('Y-m-d H:i:s'),
						'last_banned' => date('Y-m-d H:i:s'),
						'bans' => 1
					);
				}

				//Force a full ban for the user
				if($blApplyFullBan && self::$arrBanHistory[$strIPAddress]['bans'] < self::$intMaxSoftBans){
					self::$arrBanHistory[$strIPAddress]['bans'] = self::$intMaxSoftBans;
				}

				if(self::$arrBanHistory[$strIPAddress]['bans'] >= self::$intMaxSoftBans){
					//Upgrade ban to a full ban
					self::$arrBannedIPs[$strIPAddress]['reason'] = 'Reached soft ban limit';
					self::$arrBannedIPs[$strIPAddress]['length'] = self::$intFullBanSeconds;
					self::$arrBannedIPs[$strIPAddress]['expire'] = (self::$intFullBanSeconds == 0) ? date('Y-m-d H:i:s',strtotime('+10 Years')) : date('Y-m-d H:i:s',strtotime('+'.self::$intFullBanSeconds.' Seconds'));
				}

				\Twist::Cache()->write('protect/banned-ips',self::$arrBannedIPs,86400*self::$intTwistCacheLife);
				\Twist::Cache()->write('protect/ban-history',self::$arrBanHistory,86400*self::$intTwistCacheLife);

				return true;
			}

			return false;
		}

		/**
		 * Unban a banned IP address, optionally reset the ban history for this IP back to 0
		 * @param $strIPAddress
		 * @param bool $blResetBanHistory
		 */
		public static function unbanIP($strIPAddress,$blResetBanHistory = false){

			self::load();

			unset(self::$arrBannedIPs[$strIPAddress]);
			\Twist::Cache()->write('protect/banned-ips',self::$arrBannedIPs,86400*self::$intTwistCacheLife);

			if($blResetBanHistory){
				self::resetBanHistory($strIPAddress);
			}
		}

		/**
		 * Reset the ban history count for an IP address, preventing the IP going straight to Full ban next time
		 * @param $strIPAddress
		 */
		public static function resetBanHistory($strIPAddress){

			self::load();

			self::$arrBanHistory[$strIPAddress]['bans'] = 0;
			\Twist::Cache()->write('protect/ban-history',self::$arrBannedIPs,86400*self::$intTwistCacheLife);
		}

		/**
		 * Whitelist an IP address, these address will not be able to be banned at all whilst on the whitelist.
		 * This will remove all the history for the whitelisted IP address
		 * @param $strIPAddress
		 * @param string $strReason Reason or Name for the whitelisted IP address
		 */
		public static function whitelistIP($strIPAddress,$strReason = ''){

			self::load();

			self::$arrWhitelistIPs[$strIPAddress]['listed'] = date('Y-m-d H:i:s');
			self::$arrWhitelistIPs[$strIPAddress]['reason'] = $strReason;

			unset(self::$arrFailedActions[$strIPAddress]);
			unset(self::$arrBannedIPs[$strIPAddress]);
			unset(self::$arrBanHistory[$strIPAddress]);

			\Twist::Cache()->write('protect/failed-actions',self::$arrFailedActions,86400*self::$intTwistCacheLife);
			\Twist::Cache()->write('protect/banned-ips',self::$arrBannedIPs,86400*self::$intTwistCacheLife);
			\Twist::Cache()->write('protect/whitelist-ips',self::$arrWhitelistIPs,86400*self::$intTwistCacheLife);
			\Twist::Cache()->write('protect/ban-history',self::$arrBanHistory,86400*self::$intTwistCacheLife);
		}

		/**
		 * Remove an IP address from the whitelist, these IP addreses will become bannable
		 * @param $strIPAddress
		 */
		public static function unwhitelistIP($strIPAddress){

			self::load();

			unset(self::$arrWhitelistIPs[$strIPAddress]);
			\Twist::Cache()->write('protect/whitelist-ips',self::$arrWhitelistIPs,86400*self::$intTwistCacheLife);
		}
	}