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
	 * GDPR Compliance - Log the users consent to a particular type of communication, you can use the predefined types or
	 * create you own by passing in a number as your consent type.The identifier can either be a user id, phone number or
	 * email address which will be detected upon entry.
	 */
	class Consent extends Data{

		public const CONSENT_GENERAL = 0;
		public const CONSENT_NEWS = 1;
		public const CONSENT_MARKETING = 2;
		public const CONSENT_THIRDPARTY = 3;

		/**
		 * Give consent, OptIn for a particular type of communication by passing in the identifier, consent can be
		 * given by email, user id, phone number or a mixture of them all.
		 * @param $mxdIdentifier
		 * @param int $intConsentType
		 * @param null $intGivenByUserID User ID is auto detected but should be passed in if the user is logged out
		 * @return bool|int
		 * @throws \Exception
		 */
		public function give($mxdIdentifier,$intConsentType = self::CONSENT_GENERAL,$intGivenByUserID = null){

			$arrIdentifierType = $this->identifierDetection($mxdIdentifier);

			$resConsent = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'gdpr_consent')->create();

			$resConsent->set('identifier',$arrIdentifierType['identifier']);
			$resConsent->set('identifier_type',$arrIdentifierType['type']);
			$resConsent->set('consent',$intConsentType);
			$resConsent->set('given',date('Y-m-d " H:i:s'));
			$resConsent->set('given_by',(is_null($intGivenByUserID)) ? \Twist::User()->currentID() : $intGivenByUserID);

			return $resConsent->commit();
		}

		/**
		 * Revoke consent. OptOut from a particular type of communication by passing in an identifier, consent can be
		 * given by email, user id, phone number or a mixture of them all.
		 * @param $mxdIdentifier
		 * @param int $intConsentType
		 * @return bool|int
		 * @throws \Exception
		 */
		public function revoke($mxdIdentifier,$intConsentType = self::CONSENT_GENERAL){

			$arrIdentifierType = $this->identifierDetection($mxdIdentifier);

			$resResult = \Twist::Database()->query(
				"DELETE FROM `%sgdpr_consent`
						WHERE `identifier` = '%s'
						AND `identifier_type` = %d
						AND `consent` = %d
						LIMIT 1",
				TWIST_DATABASE_TABLE_PREFIX,
				$arrIdentifierType['identifier'],
				$arrIdentifierType['type'],
				$intConsentType
			);

			return $resResult->status();
		}

		/**
		 * Revoke all consent. OptOut from all communication types by passing in an identifier, consent can be
		 * given by email, user id, phone number or a mixture of them all.
		 * @param $mxdIdentifier
		 * @return bool|int
		 * @throws \Exception
		 */
		public function revokeAll($mxdIdentifier){

			$arrIdentifierType = $this->identifierDetection($mxdIdentifier);

			$resResult = \Twist::Database()->query(
				"DELETE FROM `%sgdpr_consent`
						WHERE `identifier` = '%s'
						AND `identifier_type` = %d",
				TWIST_DATABASE_TABLE_PREFIX,
				$arrIdentifierType['identifier'],
				$arrIdentifierType['type']
			);

			return $resResult->status();
		}

		/**
		 * Find out if consent has been given by passing in the identifier, consent can be given by email,
		 * user id, phone number or a mixture of them all.
		 * @param $mxdIdentifier
		 * @param int $intConsentType
		 * @return array|mixed
		 * @throws \Exception
		 */
		public function isGiven($mxdIdentifier,$intConsentType = self::CONSENT_GENERAL){

			$arrIdentifierType = $this->identifierDetection($mxdIdentifier);

			$resResult = \Twist::Database()->query(
				"SELECT * FROM `%sgdpr_consent`
						WHERE `identifier` = '%s'
						AND `identifier_type` = %d
						AND `consent` = %d
						LIMIT 1",
				TWIST_DATABASE_TABLE_PREFIX,
				$arrIdentifierType['identifier'],
				$arrIdentifierType['type'],
				$intConsentType
			);

			return ($resResult->row());
		}

		/**
		 * Get an array of all the consent that has been given for this identifier, consent can be given by email,
		 * user id, phone number or a mixture of them all. You should be able to determine who gave the consent by the
		 * given_by field.
		 * @param $mxdIdentifier
		 * @return array
		 * @throws \Exception
		 */
		public function given($mxdIdentifier){

			$arrIdentifierType = $this->identifierDetection($mxdIdentifier);

			$resResult = \Twist::Database()->query(
				"SELECT * FROM `%sgdpr_consent`
						WHERE `identifier` = '%s'
						AND `identifier_type` = %d",
				TWIST_DATABASE_TABLE_PREFIX,
				$arrIdentifierType['identifier'],
				$arrIdentifierType['type']
			);

			return $resResult->rows();
		}

		/**
		 * Expands upon the identifierDetection method in the GDPR/Data model to output an exception upon an unknown type
		 * @param $mxdIdentifier
		 * @return array
		 * @throws \Exception
		 */
		protected function identifierDetection($mxdIdentifier){

			$arrIdentifierType = parent::identifierDetection($mxdIdentifier);

			if($arrIdentifierType['type'] == self::TYPE_ERROR){
				throw new \Exception("Unknown identifier `$mxdIdentifier` passed in, cannot determine type.");
			}

			return $arrIdentifierType;
		}
	}