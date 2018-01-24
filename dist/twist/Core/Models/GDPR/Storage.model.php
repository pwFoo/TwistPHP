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

	namespace Twist\Core\Models\GDPR;

	/**
	 * GDPR compliance - Data Storage Information:
	 * Help TwistPHP understand where and how the users personal data is stored and used within the system.
	 * There is an in-built auto detection system that will allow you to get up and running with GDPR quickly
	 * and efficiently. There is also a page in the framework manager to provide further assistance.
	 */
	class Storage{

		public const USER_ID = 1;
		public const USER_EMAIL = 2;
		public const USER_PHONE = 3;
		public const USER_ALL = 4;

		protected static $arrLocations = array();

		public static function isLocation($strTable){
			return (array_key_exists($strTable,self::$arrLocations));
		}

		public static function isDataField($strTable,$srtFieldName){
			return (self::isLocation($strTable) && array_key_exists($srtFieldName,self::$arrLocations[$strTable]['fields']));
		}

		/**
		 * Manually add a data storage location to the known list of locations (Database Tables)
		 * @param $strTable
		 * @param $strUsageDescription
		 * @param $mxdPortable true Set to True will export all fields, False will export none and pass an array of fields to limit the export
		 */
		public static function addLocation($strTable,$strUsageDescription,$mxdPortable = true){

			if(!array_key_exists($strTable,self::$arrLocations)){
				self::$arrLocations[$strTable] = array(
					'fields' => array(),
					'usage' => $strUsageDescription,
					'portable' => $mxdPortable,
					'locked' => 0,
					'autodetected' => 0,
					'added' => date('Y-m-d H:i:s')
				);
			}
		}

		/**
		 * Identify fields in the storage locations that hold key data such as Email, Phone and UserID
		 * @param $strTable
		 * @param $srtFieldName
		 * @param $intType
		 * @throws \Exception
		 */
		public static function addDataField($strTable,$srtFieldName,$intType){

			if(array_key_exists($strTable,self::$arrLocations)){

				self::$arrLocations[$strTable]['fields'][$srtFieldName] = array(
					'field' => $srtFieldName,
					'type' => $intType,
					'locked' => 0,
					'autodetected' => 0,
					'added' => date('Y-m-d H:i:s')
				);

			}else{
				throw new \Exception("You must first add a storage location before adding fields to that location.");
			}
		}

		public static function removeLocation($strTable){

			if(self::isLocation($strTable) && !self::$arrLocations[$strTable]['locked']){
				unset(self::$arrLocations[$strTable]);
			}else{
				throw new \Exception("This location has not been added or is locked by TwistPHP");
			}
		}

		public static function removeDataField($strTable,$srtFieldName){

			if(self::isDataField($strTable,$srtFieldName) && !self::$arrLocations[$strTable]['fields'][$srtFieldName]['locked']){
				unset(self::$arrLocations[$strTable]['fields'][$srtFieldName]);
			}else{
				throw new \Exception("This data field has not been added or is locked by TwistPHP");
			}
		}

		/**
		 * Get a list of all the tables and run though each auto detecting where the data might be stored.
		 * We will first attempt to detect based on the field names and then using a same of the data.
		 */
		public function autodetect(){

			$arrNameDetection = array(
				'user_id' => self::USER_ID,
				'user' => self::USER_ID,
				'email' => self::USER_EMAIL,
				'email_address' => self::USER_EMAIL,
				'email_addr' => self::USER_EMAIL,
				'phone' => self::USER_PHONE,
				'phone_number' => self::USER_PHONE,
				'phonenumber' => self::USER_PHONE,
				'phoneno' => self::USER_PHONE,
				'phone_no' => self::USER_PHONE,
				'telephone' => self::USER_PHONE,
				'mobile' => self::USER_PHONE,
				'landline' => self::USER_PHONE,
			);

			$arrTables = \Twist::Database()->query("SHOW TABLES")->rows();

			if(count($arrTables) == 0){
				$arrTables = \Twist::Database()->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG='%s'",TWIST_DATABASE_NAME)->rows();

				if(count($arrTables) == 0){
					$arrTables = \Twist::Database()->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='%s'",TWIST_DATABASE_NAME)->rows();
				}
			}

			if(count($arrTables) > 0){
				//If tables have been found start the detection process
				foreach($arrTables as $strEachTable){
					$resStructure = \Twist::Database()->table($strEachTable)->get();

					$blFoundFields = false;

					//Detect based on field names
					foreach($resStructure->columns() as $strColumnName => $arrEachColumn){

						if(array_key_exists($strColumnName,$arrNameDetection)){
							//Found match, Add the field as a location
							self::autodetectAddMatch($strEachTable,$strColumnName,$resStructure->comment(),$arrNameDetection[$strColumnName]);
							$blFoundFields = true;
						}
					}

					if(!$blFoundFields){

						//Get a sample of data and detect fields that might be relevant
						$arrDataSample = \Twist::Database()->query("SELECT * FROM `%s` ORDER BY RAND() LIMIT 10",$strEachTable)->rows();

						//We need atleast 5 rows of data as we require atleast 50% matches to add a field as a match
						if(count($arrDataSample) >= 5){

							$arrPossibleEmail = array();
							$arrPossiblePhone = array();

							foreach($arrDataSample as $arrEachSample){
								foreach($arrEachSample as $strField => $strValue){

									//Detect of the vaule looks to be an email address or phone number, we cant detect userIDs at this level
									if(\Twist::Validate()->email($strValue)){
										//Found an email address
										$arrPossibleEmail[$strField]++;
									}elseif(\Twist::Validate()->telephone($strValue)){
										//Found a possible phone number
										$arrPossiblePhone[$strField]++;
									}
								}
							}

							$intThreshold = count($arrDataSample)/2;

							//Check the results to see if any matches have been found
							foreach($arrPossibleEmail as $strField => $intCount){
								if($intCount >= $intThreshold){
									//Found match, Add the phone field as a location
									self::autodetectAddMatch($strEachTable,$strField,$resStructure->comment(),self::USER_EMAIL);
								}
							}

							//Check the results to see if any matches have been found
							foreach($arrPossiblePhone as $strField => $intCount){
								if($intCount >= $intThreshold){
									//Found match, Add the phone field as a location
									self::autodetectAddMatch($strEachTable,$strField,$resStructure->comment(),self::USER_PHONE);
								}
							}
						}
					}
				}

			}else{
				throw new \Exception("Unable to detect any database tables in the database, please check the you have the correct permissions otherwise you can setup GDPR manually!");
			}
		}

		/**
		 * Protected function used to add field and location matches to the database on behalf of the autodetect function.
		 * @param $strTable
		 * @param $strField
		 * @param $strUsageDescription
		 * @param $intType
		 */
		protected static function autodetectAddMatch($strTable,$strField,$strUsageDescription,$intType){

			//add the location if not setup already
			if(!self::isLocation($strTable)){
				self::addLocation($strTable,$strUsageDescription);
				self::$arrLocations[$strTable]['autodetected'] = '1';
			}

			//Add the field to that location if not setup already
			if(!self::isDataField($strTable,$strField)){
				self::addDataField($strTable,$strField,$intType);
				self::$arrLocations[$strTable]['fields'][$strField]['autodetected'] = '1';
			}
		}
	}