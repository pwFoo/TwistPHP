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

	namespace Twist\Core\Models\Database;

	/**
	 * Simply edit/delete a mysql record from any table in an object orientated way. Commit must be called in order to may changes permanent apart from delete which will also make a permanent deletion upon method call.
	 */
	class Record{

		protected $strDatabase = null;
		protected $strTable = null;
		protected $arrOriginalRecord = array();
		protected $arrRecord = array();
		protected $arrStructure = array();

		/**
		 * Construct the class with all the required data to make usable
		 * @param string $strDatabase
		 * @param string $strTable
		 * @param array $arrStructure
		 * @param array $arrRecord
		 * @param bool $blClone
		 */
		public function __construct($strDatabase,$strTable,$arrStructure,$arrRecord,$blClone = false){
			$this->strDatabase = $strDatabase;
			$this->strTable = $strTable;
			$this->arrStructure = $arrStructure;
			$this->arrRecord = $arrRecord;
			$this->arrOriginalRecord = ($blClone) ? array() : $arrRecord;
			($blClone) ? $this->nullAutoIncrement() : null;
		}

		/**
		 * Destruct the class so it cannot be used anymore
		 */
		public function __destruct(){
			$this->strDatabase = null;
			$this->strTable = null;
			$this->arrRecord = null;
			$this->arrStructure = null;
			$this->arrOriginalRecord = null;
		}

		/**
		 * Return the auto increment field name if one exists
		 * @return int|null|string
		 */
		protected function detectAutoIncrement(){
			$strOut = null;
			foreach($this->arrStructure['columns'] as $strField => $arrOptions){
				if($arrOptions['auto_increment'] == '1'){
					$strOut = $strField;
					break;
				}
			}
			return $strOut;
		}

		/**
		 * Nullify an auto increment field, used when cloning a database record so that you wont get duplicate keys
		 */
		protected function nullAutoIncrement(){
			foreach($this->arrStructure['columns'] as $strField => $arrOptions){
				if($arrOptions['auto_increment'] == '1'){
					$this->arrRecord[$strField] = null;
					break;
				}
			}
		}

		/**
		 * Return an array of all the fields with their settings/types/lengths (without values)
		 * @return array
		 */
		public function fields(){
			return $this->arrStructure;
		}

		/**
		 * Return an associative array of key/value pairs
		 * @return array
		 */
		public function values(){
			return $this->arrRecord;
		}

		/**
		 * Get a field value
		 * @param string $strField
		 * @return null
		 */
		public function get($strField){
			return (array_key_exists($strField,$this->arrRecord)) ? $this->arrRecord[$strField] : null;
		}

		/**
		 * Set a single field in the record to a new value, you must call "->save()" to store any changes made to the database
		 * @param string $strField
		 * @param string $strValue
		 * @return bool
		 * @throws \Exception
		 */
		public function set($strField,$strValue){

			$blOut = false;

			if(array_key_exists($strField,$this->arrStructure['columns'])){
				$this->arrRecord[$strField] = $strValue;
				$blOut = true;
			}else{
				throw new \Exception(sprintf("Error adding data to database record, invalid field '%s' passed",$strField));
			}

			return $blOut;
		}

		/**
		 * Delete the record form the database table
		 * @return null
		 */
		public function delete(){

			$blOut = false;

			$strSQL = sprintf("DELETE FROM `%s`.`%s` WHERE %s LIMIT 1",
				$this->strDatabase,
				$this->strTable,
				$this->whereClause()
			);

			if(\Twist::Database()->query($strSQL)->status()){
				$this->__destruct();
				$blOut = true;
			}

			return $blOut;
		}

		/**
		 * Increment the value of a field by 1 or by the provided increment step
		 * @param string $strField
		 * @param int $intStep Value to increase the field by
		 * @return int New value of the field
		 */
		public function increment($strField,$intStep = 1){
			$intValue = $this->get($strField);
			$intValue += $intStep;
			$this->set($strField,$intValue);
			return $intValue;
		}

		/**
		 * Decrement the value of a field by 1 or by the provided increment step
		 * @param string $strField
		 * @param int $intStep Value to decrease the field by
		 * @return null New value of the field
		 */
		public function decrement($strField,$intStep = 1){
			$intValue = $this->get($strField);
			$intValue -= $intStep;
			$this->set($strField,$intValue);
			return $intValue;
		}

		/**
		 * Commit the updated record to the database table. False is returned if the query fails, a successful insert returns insertID, a successful update returns numAffectedRows or true if numAffectedRows is 0.
		 * @param bool $blInsert
		 * @return bool|int
		 */
		public function commit($blInsert = false){

			$mxdOut = true;

			if(json_encode($this->arrOriginalRecord) !== json_encode($this->arrRecord)){

				$strSQL = $this->sql($blInsert);
				$resResult = \Twist::Database()->query($strSQL);

				if($resResult->status()){
					//Now that the record has been updated in the database the original data must equal the current data
					$this->arrOriginalRecord = $this->arrRecord;

					if(substr($strSQL,0,6) === 'INSERT'){

						$mxdOut = true;
						$strAutoIncrementField = $this->detectAutoIncrement();

						if(!is_null($strAutoIncrementField)){
							$mxdOut = $resResult->insertID();

							//Update the auto increment field in the record
							$this->arrOriginalRecord[$strAutoIncrementField] = $this->arrRecord[$strAutoIncrementField] = $mxdOut;
						}

					}else if($resResult->affectedRows() !== 0){
						$mxdOut = $resResult->affectedRows();
					}
				} else {
					$mxdOut = false;
				}
			}

			return $mxdOut;
		}

		/**
		 * Get the query that will be applied, settings the second parameter true - adds as new row (default: false)
		 * @param bool $blInsert
		 * @return string
		 */
		public function sql($blInsert = false){

			$blInsert = (count($this->arrOriginalRecord) > 0) ? $blInsert : true;

			if($blInsert == true){

				$strSQL = sprintf("INSERT INTO `%s`.`%s` SET %s",
					$this->strDatabase,
					$this->strTable,
					$this->queryValues()
				);
			}else{

				$strSQL = sprintf("UPDATE `%s`.`%s` SET %s WHERE %s LIMIT 1",
					$this->strDatabase,
					$this->strTable,
					$this->queryValues(),
					$this->whereClause()
				);
			}

			return $strSQL;
		}

		/**
		 * Process the values into a usable SQL string
		 * @return string
		 */
		protected function queryValues(){

			$arrValueClause = array();

			foreach($this->arrRecord as $strField => $strValue){

				if(count($this->arrOriginalRecord) == 0 || $strValue !== $this->arrOriginalRecord[$strField]){

					//When storing/updating data allow null if field is auto increment or nullable
					if(is_null($strValue) && ($this->arrStructure['columns'][$strField]['nullable'] == '1' || $this->arrStructure['columns'][$strField]['auto_increment'] == '1')){
						$strFieldString = "`%s` = NULL";
					}else{
						//Get the correct field string for each value
						if(strstr($this->arrStructure['columns'][$strField]['data_type'],'int')){
							$strFieldString = "`%s` = %d";
						}else{
							$strFieldString = "`%s` = '%s'";
						}
					}

					$arrValueClause[] = sprintf($strFieldString, \Twist::Database()->escapeString($strField), \Twist::Database()->escapeString($strValue));
				}
			}

			return implode(', ',$arrValueClause);
		}

		/**
		 * Process the values into a usable where clause
		 * @return string
		 */
		protected function whereClause(){

			$arrWhereClause = array();

			//It would be possible to detect for unique keys here to minimize where clause id no autoincrement is set.
			$strAutoIncrementField = $this->detectAutoIncrement();

			if(!is_null($strAutoIncrementField)){
				$arrWhereClause[] = sprintf("`%s` = %d", \Twist::Database()->escapeString($strAutoIncrementField), \Twist::Database()->escapeString($this->arrOriginalRecord[$strAutoIncrementField]));
			}else{
				foreach($this->arrOriginalRecord as $strField => $strValue){

					if(is_null($strValue) && $this->arrStructure['columns'][$strField]['nullable'] == '1'){
						$strFieldString = "`%s` IS NULL";
					}else{
						//Get the correct field string for each value
						if(strstr($this->arrStructure['columns'][$strField]['data_type'],'int')){
							$strFieldString = "`%s` = %d";
						}else{
							$strFieldString = "`%s` = '%s'";
						}
					}

					$arrWhereClause[] = sprintf($strFieldString, \Twist::Database()->escapeString($strField), \Twist::Database()->escapeString($strValue));
				}
			}

			return implode(' AND ',$arrWhereClause);
		}
	}
