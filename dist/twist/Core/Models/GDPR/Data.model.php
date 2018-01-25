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
	 * GDPR compliance - Data Processing:
	 * Handel the export, packaging and removal of data as and when requested by the main GDPR helper
	 */
	class Data{

		public static const TYPE_ERROR = null;
		public static const TYPE_AUTODETECT = 0;
		public static const TYPE_ID = 1;
		public static const TYPE_EMAIL = 2;
		public static const TYPE_PHONE = 3;

		/**
		 * @var Storage
		 */
		protected $resStorage = null;

		public function _construct(){
			$this->resStorage = new Storage();
		}

		/**
		 * Get all associated data by is Identifier, default action will be to auto detect what Identifier
		 * is being used. i.e User ID, Email or Phone. The type value can be set if already known.
		 * @param $mxdIdentifier
		 * @param int $intType
		 * @return array
		 */
		public function find($mxdIdentifier,$intType = self::TYPE_AUTODETECT){

			if($intType === self::TYPE_AUTODETECT){
				$arrIdentifier = $this->identifierDetection($mxdIdentifier);
				$intType = $arrIdentifier['type'];
				$mxdIdentifier = $arrIdentifier['identifier'];
			}

			//Respond with a error if undetected type
			if($intType === self::TYPE_ERROR){
				return array(
					'status' => false,
					'error' => 'Unable to autodetect data type of the identifier: '.$mxdIdentifier
				);
			}

			return $this->data();
		}

		/**
		 * Remove all associated data by is Identifier, default action will be to auto detect what Identifier
		 * is being used. i.e User ID, Email or Phone. The type value can be set if already known.
		 * @param $mxdIdentifier
		 * @param int $intType
		 * @return array
		 */
		public function remove($mxdIdentifier,$intType = self::TYPE_AUTODETECT){

			if($intType === self::TYPE_AUTODETECT){
				$arrIdentifier = $this->identifierDetection($mxdIdentifier);
				$intType = $arrIdentifier['type'];
				$mxdIdentifier = $arrIdentifier['identifier'];
			}

			//Respond with a error if undetected type
			if($intType === self::TYPE_ERROR){
				return array(
					'status' => false,
					'error' => 'Unable to autodetect data type of the identifier: '.$mxdIdentifier
				);
			}

			return $this->data();
		}

		/**
		 * Profile the data based on the Identifier, see if we can link the Identifier to other identifiers.
		 * Will return a list of possible matches such as User ID's Phone numbers and Email addresses
		 * @param $mxdIdentifier
		 * @param int $intType
		 * @return array
		 */
		public function profile($mxdIdentifier,$intType = self::TYPE_AUTODETECT){

			if($intType === self::TYPE_AUTODETECT){
				$arrIdentifier = $this->identifierDetection($mxdIdentifier);
				$intType = $arrIdentifier['type'];
				$mxdIdentifier = $arrIdentifier['identifier'];
			}

			//Respond with a error if undetected type
			if($intType === self::TYPE_ERROR){
				return array(
					'status' => false,
					'error' => 'Unable to autodetect data type of the identifier: '.$mxdIdentifier
				);
			}

			return $this->data();
		}

		protected function data(){

			$arrOut = array();

			return $arrOut;
		}

		/**
		 * Detect what data type the provided identifier is, return a type error is undetectable
		 * @param $mxdIdentifier
		 * @return array
		 */
		protected function identifierDetection($mxdIdentifier){

			//Detect for an email address
			$mxdSanitisedIdentifier = \Twist::Validate()->email($mxdIdentifier);
			if($mxdSanitisedIdentifier !== false){
				return array(
					'type' => self::TYPE_EMAIL,
					'identifier' => $mxdSanitisedIdentifier
				);
			}

			//Detect for a phone number
			$mxdSanitisedIdentifier = \Twist::Validate()->telephone($mxdIdentifier);
			if($mxdSanitisedIdentifier !== false){
				return array(
					'type' => self::TYPE_PHONE,
					'identifier' => $mxdSanitisedIdentifier
				);
			}

			//Detect for a user ID
			$mxdSanitisedIdentifier = \Twist::Validate()->integer($mxdIdentifier);
			if($mxdSanitisedIdentifier !== false){
				return array(
					'type' => self::TYPE_ID,
					'identifier' => $mxdSanitisedIdentifier
				);
			}

			//Return a detection failure
			return array(
				'type' => self::TYPE_ERROR,
				'identifier' => $mxdIdentifier
			);
		}
	}