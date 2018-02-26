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

		public const TYPE_ERROR = null;
		public const TYPE_AUTODETECT = 0;
		public const TYPE_ID = 1;
		public const TYPE_EMAIL = 2;
		public const TYPE_PHONE = 3;
		public const TYPE_POSTCODE = 4;
		public const TYPE_FULLNAME = 5;
		public const TYPE_FIRSTNAME = 6;
		public const TYPE_SURNAME = 7;
		public const TYPE_DOB = 8;
		public const TYPE_GENDER = 9;
		public const TYPE_EMPLOYER = 10;
		public const TYPE_ROLE = 11;

		public const PROFILER_INVESTIGATE_POSTCODE = false;
		public const PROFILER_INVESTIGATE_FIRSTNAME = false;
		public const PROFILER_INVESTIGATE_SURNAME = false;
		public const PROFILER_INVESTIGATE_FULLNAME = false;
		public const PROFILER_INVESTIGATE_DOB = false;
		public const PROFILER_INVESTIGATE_EMPLOYER = false;
		public const PROFILER_INVESTIGATE_ROLE = false;
		public const PROFILER_RUN_LIMIT = 15;

		public const DEFAULT_PROFILE = array(
			'user_id' => 0,
			'personal' => array(
				'fullname' => '',
				'firstname' => '',
				'surname' => '',
				'dob' => '',
				'gender' => '',
				'employer' => '',
				'role' => ''
			),
			'email' => array(),
			'phone' => array(),
			'postcode' => array(),
			'associations' => array(),
			'response' => array(
				'status' => false,
				'message' => 'No data has been profiled',
				'records' => 0,
				'portable' => 0
			)
		);

		/**
		 * @var Storage
		 */
		protected $resStorage = null;

		protected $arrProfile = array();
		protected $arrProfiledData = array();
		protected $arrProfiledIdentifiers = array();
		protected $intProfilerRuns = 0;

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
			$this->profile($mxdIdentifier,$intType);
			return $this->arrProfiledData;
		}

		/**
		 * Remove all associated data by is Identifier, default action will be to auto detect what Identifier
		 * is being used. i.e User ID, Email or Phone. The type value can be set if already known.
		 * @param $mxdIdentifier
		 * @param int $intType
		 * @return array
		 */
		public function remove($mxdIdentifier,$intType = self::TYPE_AUTODETECT){

			$this->profile($mxdIdentifier,$intType);

			//Write the code to remove all the data

			return true;
		}

		/**
		 * Profile the data based on the Identifier, see if we can link the Identifier to other identifiers.
		 * Will return a list of possible matches such as User ID's Phone numbers and Email addresses
		 * @param $mxdIdentifier
		 * @param int $intType
		 * @return array
		 */
		public function profile($mxdIdentifier,$intType = self::TYPE_AUTODETECT){

			//Reset the profiler
			$this->arrProfile = self::DEFAULT_PROFILE;
			$this->arrProfiledData = array();
			$this->arrProfiledIdentifiers = array();

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

			$this->updateProfile($intType,$mxdIdentifier);
			$this->arrProfile['response']['status'] = $this->runProfileQueue();

			if(!$this->arrProfile['response']['status']){
				$this->arrProfile['response']['message'] = 'The profiler has reached its run limit of '.self::PROFILER_RUN_LIMIT.' iterations';
			}else{
				$this->arrProfile['response']['message'] = "Full profile analysis of `{$mxdIdentifier}` complete";
			}

			return $this->arrProfile;
		}

		public function profileData(){
			return $this->arrProfiledData;
		}

		protected function runProfileQueue(){

			$intCount = count($this->arrProfiledIdentifiers);

			foreach($this->arrProfiledIdentifiers as $mxdIdentifier => $arrIdentifierInfo){

				if($arrIdentifierInfo['status'] == false){
					$this->intProfilerRuns++;

					$this->getData($arrIdentifierInfo['type'],$mxdIdentifier);
					$this->arrProfiledIdentifiers[$mxdIdentifier]['status'] = true;
				}
			}

			if($this->intProfilerRuns >= self::PROFILER_RUN_LIMIT){
				//If the run limit has been reached and no more identifiers where found all is good, else fail
				return (count($this->arrProfiledIdentifiers) == $intCount) ? true : false;
			}

			//If there are new identifiers re-run the profiler
			if(count($this->arrProfiledIdentifiers) > $intCount){
				return $this->runProfileQueue();
			}

			return true;
		}

		protected function updateProfile($intType,$mxdIdentifier){

			switch($intType){

				case self::TYPE_FULLNAME:
					$this->arrProfile['personal']['fullname'] = $mxdIdentifier;
					break;

				case self::TYPE_FIRSTNAME:

					if($this->arrProfile['personal']['firstname'] == ''){
						if($this->arrProfile['personal']['surname'] == ''){
							$this->arrProfile['personal']['fullname'] = $mxdIdentifier;
						}else{
							$this->arrProfile['personal']['fullname'] = $mxdIdentifier.' '.$this->arrProfile['personal']['fullname'];
						}
						$this->arrProfile['personal']['firstname'] = $mxdIdentifier;
					}
					break;

				case self::TYPE_SURNAME:

					if($this->arrProfile['personal']['surname'] == ''){
						if($this->arrProfile['personal']['firstname'] == ''){
							$this->arrProfile['personal']['fullname'] = $mxdIdentifier;
						}else{
							$this->arrProfile['personal']['fullname'] .= ' '.$mxdIdentifier;
						}
						$this->arrProfile['personal']['surname'] = $mxdIdentifier;
					}
					break;

				case self::TYPE_ID:

					if($this->arrProfile['user_id'] == 0){
						$this->arrProfile['user_id'] = $mxdIdentifier;
					}else{
						//Found associated User
					}
					break;

				case self::TYPE_EMAIL:

					if(!in_array($mxdIdentifier,$this->arrProfile['email'])){
						$this->arrProfile['email'][] = $mxdIdentifier;
					}
					break;

				case self::TYPE_PHONE:

					if(!in_array($mxdIdentifier,$this->arrProfile['phone'])){
						$this->arrProfile['phone'][] = $mxdIdentifier;
					}
					break;

				case self::TYPE_POSTCODE:

					if(!in_array($mxdIdentifier,$this->arrProfile['postcode'])){
						$this->arrProfile['postcode'][] = $mxdIdentifier;
					}
					break;

				case self::TYPE_DOB:
					$this->arrProfile['personal']['dob'] = $mxdIdentifier;
					break;

				case self::TYPE_EMPLOYER:
					$this->arrProfile['personal']['employer'] = $mxdIdentifier;
					break;

				case self::TYPE_ROLE:
					$this->arrProfile['personal']['role'] = $mxdIdentifier;
					break;
			}

			//Add the indentifier to the profiled list with a false flag i.e. Still to be processed
			if(!array_key_exists($mxdIdentifier,$this->arrProfiledIdentifiers)){

				//Only investigate postcode and names if the profiler has been explicitly enabled as they can though up coincidental associations
				if(($intType == self::TYPE_POSTCODE && !self::PROFILER_INVESTIGATE_POSTCODE) ||
					($intType == self::TYPE_FIRSTNAME && !self::PROFILER_INVESTIGATE_FIRSTNAME) ||
					($intType == self::TYPE_SURNAME && !self::PROFILER_INVESTIGATE_SURNAME) ||
					($intType == self::TYPE_FULLNAME && !self::PROFILER_INVESTIGATE_FULLNAME) ||
					($intType == self::TYPE_DOB && !self::PROFILER_INVESTIGATE_DOB) ||
					($intType == self::TYPE_EMPLOYER && !self::PROFILER_INVESTIGATE_EMPLOYER) ||
					($intType == self::TYPE_ROLE && !self::PROFILER_INVESTIGATE_ROLE)){

					//Don't investigate these items

				}else{

					$this->arrProfiledIdentifiers[$mxdIdentifier] = array(
						'status' => false,
						'type' => $intType,
						'identifier' => $mxdIdentifier
					);
				}
			}
		}

		protected function getData($intType,$mxdIdentifier){

			$resStorage = new Storage();

			foreach($resStorage->locations() as $strTable => $arrLocationInfo){

				//Find the field name for this type
				foreach($arrLocationInfo['fields'] as $arrEachField){

					if($intType == $arrEachField['type']){
						foreach(\Twist::Database()->records($strTable)->find($mxdIdentifier,$arrEachField['field']) as $arrFields){

							$this->updateProfile($intType,$arrFields[$arrEachField['field']]);

							//If there is more than 1 identifier in this table profile all relevant data
							if(count($arrLocationInfo['fields']) >  1){
								foreach($arrLocationInfo['fields'] as $arrEachAdditionalField){
									$this->updateProfile($arrEachAdditionalField['type'],$arrFields[$arrEachAdditionalField['field']]);
								}
							}

							$this->arrProfiledData[$strTable][sha1(json_encode($arrFields,true))] = $arrFields;
							$this->arrProfile['response']['records']++;

							if($arrLocationInfo['portable'] == 1){
								$this->arrProfile['response']['portable']++;
							}
						}
						break;
					}
				}
			}
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

			//Detect for a phone number, ignore unix date strings just incase
			$mxdSanitisedIdentifier = \Twist::Validate()->telephone($mxdIdentifier);
			if($mxdSanitisedIdentifier !== false && !preg_match("#^([0-9]{4})([\s\-\.]{1})([0-9]{2})([\s\-\.]{1})([0-9]{2})(\s([0-9]{2})\:([0-9]{2})\:([0-9]{2}))?$#",$mxdSanitisedIdentifier,$arrMatches)){
				return array(
					'type' => self::TYPE_PHONE,
					'identifier' => $mxdSanitisedIdentifier
				);
			}

			//Detect for a postcode
			$mxdSanitisedIdentifier = \Twist::Validate()->postcode($mxdIdentifier);
			if($mxdSanitisedIdentifier !== false){
				return array(
					'type' => self::TYPE_POSTCODE,
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