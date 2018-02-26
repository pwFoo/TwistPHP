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
	 * GDPR compliance - Log Users Consent
	 * Log consent for the use of users data, which includes email and phone number
	 */
	class Consent{

		public const CONSENT_GENERAL = 0;
		public const CONSENT_NEWS = 1;
		public const CONSENT_MARKETING = 2;
		public const CONSENT_THIRDPARTY = 3;

		/**
		 * Give general consent for system communications with a user
		 * @param $mxdIdentifier
		 * @param $intUserID
		 */
		public function give($mxdIdentifier,$intUserID = 0){

		}

		/**
		 * Double Optin for a particular type off communication
		 * @param $mxdIdentifier
		 * @param $intConsentType
		 * @param $intUserID
		 */
		public function optin($mxdIdentifier,$intConsentType,$intUserID = 0){

		}

		/**
		 * Find out if consent has been given
		 * @param $intConsentType
		 * @param $intType
		 */
		public function given($mxdIdentifier,$intConsentType){

		}

	}