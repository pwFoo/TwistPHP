<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
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

namespace Twist\Core\Models\Email;

class ProtocolSMTP extends BaseProtocol{

	/**
	 * Open a new FTP connection
	 * @param string $strHost
	 * @param integer $intPort
	 * @return boolean
	 */
	public function connect($strHost,$intPort = 25){

		$arrContextOptions = array();

		$intErrorNo = 0;
		$strErrorMessage = '';

		if(in_array($intPort,array(465,587)) && !strstr($strHost,'ssl://')){
			$strHost = 'ssl://'.$strHost;
		}

		if(function_exists('stream_socket_client')){

			$cxtStreamSocket = stream_context_create($arrContextOptions);

			$this->resConnection = stream_socket_client(
				sprintf('%s:%d',$strHost,$intPort),
				$intErrorNo,
				$strErrorMessage,
				$this->intTimeout,
				STREAM_CLIENT_CONNECT,
				$cxtStreamSocket
			);

		}else{

			$this->resConnection = fsockopen(
				$strHost,
				$intPort,
				$intErrorNo,
				$strErrorMessage,
				$this->intTimeout
			);

			stream_set_blocking($this->resConnection, true);
			stream_set_timeout($this->resConnection, $this->intTimeout,0);
		}

		if($intErrorNo != 0 || !is_resource($this->resConnection)){
			$this->setError($intErrorNo,$strErrorMessage);
			return false;
		}

		$arrResponse = $this->request();
		if($arrResponse['code'] !== 220){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		$this->blConnected = true;
		return true;
	}

	/**
	 * Disconnect the current session (connection)
	 */
	public function disconnect(){

		$arrResponse = $this->request('QUIT');
		if($arrResponse['code'] !== 221){
			$this->setError($arrResponse['code'],$arrResponse['data']);
		}

		fclose($this->resConnection);
		$this->resConnection = null;
		$this->blConnected = false;
	}

	public function login($strEmailAddress,$strPassword){

		list($strLocalPart,$strEmailHost) = explode('@',$strEmailAddress);

		$arrResponse = $this->request(sprintf('EHLO %s',$strEmailHost));
		if($arrResponse['code'] !== 250){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		$arrResponse = $this->request('AUTH LOGIN');
		if($arrResponse['code'] !== 334){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		$arrResponse = $this->request(base64_encode($strEmailAddress));
		if($arrResponse['code'] !== 334){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		$arrResponse = $this->request(base64_encode($strPassword));
		if($arrResponse['code'] !== 235){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		return true;
	}

	public function from($strFromAddress){

		$arrResponse = $this->request(sprintf('MAIL FROM: %s',$strFromAddress));
		if($arrResponse['code'] !== 250){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		return true;
	}

	public function to($strToAddress){

		$arrResponse = $this->request(sprintf('RCPT TO: %s',$strToAddress));
		if($arrResponse['code'] !== 250){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		return true;
	}

	public function send($strEmailSource){

		//Send the initiator for the email source to be passed
		$arrResponse = $this->request('DATA');
		if($arrResponse['code'] !== 354){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		//Send the email source
		$this->request($strEmailSource,true);

		//Send the end signal for the email data source
		$arrResponse = $this->request('.');
		if($arrResponse['code'] !== 250){
			$this->setError($arrResponse['code'],$arrResponse['data']);
			return false;
		}

		return true;
	}

	/**
	 * Send and read data from the FTP connection, this function is used to send and receive all comms with the FTP server.
	 * @param null $strRequestString
	 * @param boolean $blWriteOnly
	 * @return array
	 */
	protected function request($strRequestString = null,$blWriteOnly = false){

		$intStartTime = time();
		$arrResponse = array(
			'data' => '',
			'code' => 0
		);

		//PUT the request data if required, otherwise get the response data only
		if(!is_null($strRequestString)){
			$this->strMessageLog .= "> ".$strRequestString."\n";
			fputs($this->resConnection, $strRequestString."\r\n");
		}

		if($blWriteOnly == false){

			while (is_resource($this->resConnection) && !feof($this->resConnection)){

				$mxdLine = fgets($this->resConnection, 515);
				$arrResponse['data'] .= $mxdLine;
				$this->strMessageLog .= $mxdLine;

				if(preg_match('#^([0-9]{3})\s#',$mxdLine,$arrMatches)){
					$arrResponse['code'] = intval($arrMatches[0]);
					break;
				}

				//Check for a socket timeout
				$arrMetaInfo = stream_get_meta_data($this->resConnection);
				if(array_key_exists('timed_out',$arrMetaInfo) && $arrMetaInfo['timed_out']){
					$this->setError(0,'Request timeout');
					break;
				}

				//Check for a hard timeout to prevent endless loops
				if((time() - $intStartTime) > $this->intTimeout){
					$this->setError(0,'Request timeout - Hard time limit reached');
					break;
				}
			}

			$this->strLastResponse = $arrResponse['data'];
		}

		return $arrResponse;
	}
}