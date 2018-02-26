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
		
		protected $arrLocations = array();

		public function __construct(){
			$this->load();
		}

		/**
		 * Load all the locations from the database and expand into usable data
		 */
		public function load(){

			//Get all the locations from the database
			$this->arrLocations = \Twist::framework()->tools()->arrayReindex(
				\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'gdpr_locations')->all(),
				'table'
			);

			//Expand all the fields back into the table array
			foreach($this->arrLocations as $arrData){
				$this->arrLocations[$arrData['table']]['fields'] = json_decode($arrData['fields'],true);
			}
		}

		public function reset(){
			$this->arrLocations = array();
		}

		/**
		 * Store all changes to the database
		 * @return bool
		 */
		public function commit(){

			//Remove all the current records
			\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'gdpr_locations')->delete(null,null,null);

			foreach($this->arrLocations as $strTable => $arrData){

				$resLocation = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'gdpr_locations')->create();
				$resLocation->set('table',$strTable);
				$resLocation->set('usage',$arrData['usage']);
				$resLocation->set('portable',$arrData['portable']);
				$resLocation->set('locked',$arrData['locked']);
				$resLocation->set('autodetected',$arrData['autodetected']);
				$resLocation->set('added',$arrData['added']);
				$resLocation->set('fields',json_encode($arrData['fields']));
				$resLocation->commit();
			}

			return true;
		}

		public function locations(){
			return $this->arrLocations;
		}

		public function isLocation($strTable){
			return (array_key_exists($strTable,$this->arrLocations));
		}

		public function isDataField($strTable,$srtFieldName){
			return ($this->isLocation($strTable) && array_key_exists($srtFieldName,$this->arrLocations[$strTable]['fields']));
		}

		/**
		 * Manually add a data storage location to the known list of locations (Database Tables)
		 * @param $strTable
		 * @param $strUsageDescription
		 * @param $mxdPortable true Set to True will export all fields, False will export none and pass an array of fields to limit the export
		 */
		public function addLocation($strTable,$strUsageDescription,$mxdPortable = true){

			if(!array_key_exists($strTable,$this->arrLocations)){

				$this->arrLocations[$strTable] = array(
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
		public function addDataField($strTable,$srtFieldName,$intType){

			if(array_key_exists($strTable,$this->arrLocations)){

				$this->arrLocations[$strTable]['fields'][$srtFieldName] = array(
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

		public function removeLocation($strTable){

			if($this->isLocation($strTable) && !$this->arrLocations[$strTable]['locked']){
				unset($this->arrLocations[$strTable]);
			}else{
				throw new \Exception("This location has not been added or is locked by TwistPHP");
			}
		}

		public function removeDataField($strTable,$srtFieldName){

			if($this->isDataField($strTable,$srtFieldName) && !$this->arrLocations[$strTable]['fields'][$srtFieldName]['locked']){
				unset($this->arrLocations[$strTable]['fields'][$srtFieldName]);
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
				'user_id' => Data::TYPE_ID,
				'user' => Data::TYPE_ID,
				'email' => Data::TYPE_EMAIL,
				'email_address' => Data::TYPE_EMAIL,
				'email_addr' => Data::TYPE_EMAIL,
				'phone' => Data::TYPE_PHONE,
				'phone_number' => Data::TYPE_PHONE,
				'phonenumber' => Data::TYPE_PHONE,
				'phoneno' => Data::TYPE_PHONE,
				'phone_no' => Data::TYPE_PHONE,
				'telephone' => Data::TYPE_PHONE,
				'mobile' => Data::TYPE_PHONE,
				'landline' => Data::TYPE_PHONE,
				'post_code' => Data::TYPE_POSTCODE,
				'postcode' => Data::TYPE_POSTCODE,
				'postal_code' => Data::TYPE_POSTCODE,
				'postalcode' => Data::TYPE_POSTCODE,
				'firstname' => Data::TYPE_FIRSTNAME,
				'first_name' => Data::TYPE_FIRSTNAME,
				'forename' => Data::TYPE_FIRSTNAME,
				'christian_name' => Data::TYPE_FIRSTNAME,
				'surname' => Data::TYPE_SURNAME,
				'familyname' => Data::TYPE_SURNAME,
				'family_name' => Data::TYPE_SURNAME,
				'fullname' => Data::TYPE_FULLNAME,
				'dob' => Data::TYPE_DOB,
				'date_of_birth' => Data::TYPE_DOB,
				'dateofbirth' => Data::TYPE_DOB,
				'birthday' => Data::TYPE_DOB,
				'employer' => Data::TYPE_EMPLOYER,
				'company' => Data::TYPE_EMPLOYER,
				'role' => Data::TYPE_ROLE,
				'job' => Data::TYPE_ROLE,
				'job_title' => Data::TYPE_ROLE,
				'jobtitle' => Data::TYPE_ROLE
			);

			$arrTablesRaw = \Twist::Database()->query("SHOW TABLES")->rows();

			if(count($arrTablesRaw) == 0){
				$arrTablesRaw = \Twist::Database()->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG='%s'",TWIST_DATABASE_NAME)->rows();

				if(count($arrTablesRaw) == 0){
					$arrTablesRaw = \Twist::Database()->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='%s'",TWIST_DATABASE_NAME)->rows();
				}

				$arrTables = array();
				foreach($arrTablesRaw as $arrTable){
					$arrTables[] = $arrTable['TABLE_NAME'];
				}

			}else{

				$strTableKey = array_keys($arrTablesRaw[0])[0];
				$arrTables = array();
				foreach($arrTablesRaw as $arrTable){
					$arrTables[] = $arrTable[$strTableKey];
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
							$this->autodetectAddMatch($strEachTable,$strColumnName,$resStructure->comment(),$arrNameDetection[$strColumnName]);
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
							$arrPossiblePostcode = array();

							foreach($arrDataSample as $arrEachSample){
								foreach($arrEachSample as $strField => $strValue){

									//Detect of the vaule looks to be an email address or phone number, we cant detect userIDs at this level
									if(\Twist::Validate()->email($strValue)){
										//Found an email address
										$arrPossibleEmail[$strField]++;
									}elseif(\Twist::Validate()->telephone($strValue) && !preg_match("#^([0-9]{4})([\s\-\.]{1})([0-9]{2})([\s\-\.]{1})([0-9]{2})(\s([0-9]{2})\:([0-9]{2})\:([0-9]{2}))?$#",$strValue,$arrMatches)){
										//Found a possible phone number
										$arrPossiblePhone[$strField]++;
									}elseif(\Twist::Validate()->postcode($strValue)){
										//Found a possible phone number
										$arrPossiblePostcode[$strField]++;
									}
								}
							}

							$intThreshold = count($arrDataSample)/2;

							//Check the results to see if any matches have been found
							foreach($arrPossibleEmail as $strField => $intCount){
								if($intCount >= $intThreshold){
									//Found match, Add the phone field as a location
									$this->autodetectAddMatch($strEachTable,$strField,$resStructure->comment(),Data::TYPE_EMAIL);
								}
							}

							//Check the results to see if any matches have been found
							foreach($arrPossiblePhone as $strField => $intCount){
								if($intCount >= $intThreshold){
									//Found match, Add the phone field as a location
									$this->autodetectAddMatch($strEachTable,$strField,$resStructure->comment(),Data::TYPE_PHONE);
								}
							}

							//Check the results to see if any matches have been found
							foreach($arrPossiblePostcode as $strField => $intCount){
								if($intCount >= $intThreshold){
									//Found match, Add the postcode field as a location
									$this->autodetectAddMatch($strEachTable,$strField,$resStructure->comment(),Data::TYPE_POSTCODE);
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
		protected function autodetectAddMatch($strTable,$strField,$strUsageDescription,$intType){

			//add the location if not setup already
			if(!$this->isLocation($strTable)){
				$this->addLocation($strTable,$strUsageDescription);
				$this->arrLocations[$strTable]['autodetected'] = '1';
			}

			//Add the field to that location if not setup already
			if(!$this->isDataField($strTable,$strField)){
				$this->addDataField($strTable,$strField,$intType);
				$this->arrLocations[$strTable]['fields'][$strField]['autodetected'] = '1';
			}
		}
	}