<?php
	/**
	 * This file is part of TwistPHP.
	 *
	 * TwistPHP is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * TwistPHP is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP\Packages;
	use TwistPHP\ModuleBase;

	/**
	 * Localisation of websites is becoming a necessity, the ability to list counties, languages and there relationship is essential.
	 * Get a full list of countries and their ISO codes. Get the native spoken language of a country by its ISO code. Get the name of
	 * a language by its ISO language code.
	 */
	class Localisation extends ModuleBase{

		protected $arrLanguages = array();
		protected $arrLanguagesLocalised = array();
		protected $arrCountries = array();
		protected $arrTimezones = array();

		public function __construct(){

			$jsonLanguage = file_get_contents(sprintf('%score/packages/resources/Localisation/languages.json',DIR_FRAMEWORK));
			$arrLanguages = json_decode($jsonLanguage,true);

			//Build the array of languages to include those with variants
			foreach($arrLanguages as $arrEachLanguage){
				if(!is_null($arrEachLanguage['variant'])){
					$strKey = sprintf("%s-%s",$arrEachLanguage['iso'],$arrEachLanguage['variant']);
					$this->arrLanguagesLocalised[strtolower($strKey)] = $arrEachLanguage;
				}else{
					$this->arrLanguagesLocalised[strtolower($arrEachLanguage['iso'])] = $arrEachLanguage;
					$this->arrLanguages[strtolower($arrEachLanguage['iso'])] = $arrEachLanguage;
				}
			}

			$jsonCountries = file_get_contents(sprintf('%score/packages/resources/Localisation/countries.json',DIR_FRAMEWORK));
			$arrCountries = json_decode($jsonCountries,true);

			foreach($arrCountries as $arrEachCountry){
				$this->arrCountries[strtolower($arrEachCountry['iso'])] = $arrEachCountry;
			}

			$jsonTimezones = file_get_contents(sprintf('%score/packages/resources/Localisation/timezones.json',DIR_FRAMEWORK));
			$this->arrTimezones = json_decode($jsonTimezones,true);
		}

		/**
		 * Get language by 2 or 5 car language code
		 * @param $strLanguageISO
		 * @return array
		 */
		public function getLanguage($strLanguageISO){
			$strLanguageISO = strtolower($strLanguageISO);
			return (array_key_exists($strLanguageISO,$this->arrLanguagesLocalised)) ? $this->arrLanguagesLocalised[$strLanguageISO] : array();
		}

		/**
		 * Get all languages, optionally include the localised version of languages
		 * @param bool $blIncludeLocalisation
		 * @return array
		 */
		public function getLanguages($blIncludeLocalisation = false){
			return ($blIncludeLocalisation) ? $this->arrLanguagesLocalised : $this->arrLanguages;
		}

		/**
		 * Get country by its 2 char country code
		 * @param $strCountryISO
		 * @return array
		 */
		public function getCountry($strCountryISO){
			$strCountryISO = strtolower($strCountryISO);
			return (array_key_exists($strCountryISO,$this->arrCountries)) ? $this->arrCountries[$strCountryISO] : array();
		}

		/**
		 * Get an array of all countries
		 * @return array
		 */
		public function getCountries(){
			return $this->arrCountries;
		}

		/**
		 * Get the offical language of any given country by 2 char country code
		 * @param $strCountryISO
		 * @return array
		 */
		public function getOfficialLanguage($strCountryISO){
			$arrCountry = $this->getCountry($strCountryISO);
			return (count($arrCountry)) ? $this->getLanguage($arrCountry['official_language_iso']) : array();
		}
	}