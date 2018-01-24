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

	namespace Twist\Core\Helpers;

	/**
	 * Ensure that the framework offers GDPR compliance features:
	 * - Breach Notification
	 * - Right to Access
	 * - Right to be Forgotten
	 * - Data Portability
	 */
	class GDPR extends Base{

		/**
		 * = Breach Notification =
		 * Send out an email notification to all registered users that a data breach has happened,
		 * the email is pre-formatted with the additon of the message passed in, set the test
		 * parameter to true and the example message will be displayed on screen.
		 * @param $strMessage
		 * @param bool $blTest
		 */
		public function breachNotification($strMessage,$blTest = false){

		}

		/**
		 * = Right to Access =
		 * Detect if any of the users personal data is held within the system, if data
		 * is detected, a list of all the tables were the data has been detected is
		 * returned. You can pass in a phone number, email address or user ID to be searched.
		 * @param $mxdUserIdentifier
		 */
		public function rightToAccess($mxdUserIdentifier){

		}

		/**
		 * = Right to be Forgotten =
		 * Remove all data associated of any particular user from the system, an email will be
		 * sent to the user to confirm removal of the data (Where an email address is held).
		 * @param $mxdUserIdentifier
		 */
		public function rightToBeForgotten($mxdUserIdentifier){

		}

		/**
		 * = Data Portability =
		 * All data held about a user will be exported in a CSV, JSON or XML format, there may be multiple files
		 * exported in which case the output will be zipped where possible. A unique link to the data will be
		 * emailed to the user upon completion.
		 * @param $mxdUserIdentifier
		 */
		public function dataPortability($mxdUserIdentifier){

		}

	}