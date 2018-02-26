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
	 * GDPR compliance - Portable Data
	 * Convert all the found data into a portable package that can be emailed to the user
	 */
	class Portable{

		public const EXPORT_CVS = 0;
		public const EXPORT_HTML = 1;
		public const EXPORT_XML = 2;

		public function export($mxdIdentifier,$intExportType = self::EXPORT_XML,$blSingleFile = true){

			$resData = new Data();
			$arrProfile = $resData->profile($mxdIdentifier);
			$arrData = $resData->profileData();

			foreach($arrData as $strTable => $arrDataRows){
				$this->pack($strTable,$arrDataRows);
			}

			if($blSingleFile){

			}else{

			}
		}

		protected function pack(){

		}

		public function import(){

		}

		protected function unpack(){

		}
	}