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
	 * @link       https://twistphp.com
	 *
	 */

	namespace Twist\Core\Classes;
	use Twist\Core\Classes\Error;

	/**
	 * Debugging the framework and its modules, functionality to access debug data can be found here. Data will only be present if Debugging is enabled in your settings.
	 */
	final class Debug{

		protected $resTemplate = null;
		public $arrDebugLog = array();

		public function __construct(){

		}

		/**
		 * Log some debug data
		 * @param $strSystem
		 * @param $strType
		 * @param $mxdData
		 */
		public function log($strSystem,$strType,$mxdData){

			if(!array_key_exists($strSystem,$this->arrDebugLog)){
				$this->arrDebugLog[$strSystem] = array();
			}

			if(!array_key_exists($strType,$this->arrDebugLog[$strSystem])){
				$this->arrDebugLog[$strSystem][$strType] = array();
			}

			$this->arrDebugLog[$strSystem][$strType][] = $mxdData;
		}

		public function window($arrCurrentRoute){

			//print_r($this->arrDebugLog);

			$arrTimer = \Twist::getEvents(true);

			$this->resTemplate = \Twist::View('TwistDebugBar');
			$this->resTemplate->setDirectory( sprintf('%sdebug/',DIR_FRAMEWORK_VIEWS));

			$arrTags = array(
				'errors' => '',
				'warning_count' => 0,
				'notice_count' => 0,
				'other_count' => 0,
				'errors' => '',
				'database_queries' => '',
				'database_query_count' => '',
				'views' => '',
				'timeline' => '',
				'execution_time' => '',
				'cache' => '',
				'memory' => $arrTimer['memory']
			);

			foreach($this->arrDebugLog['Error']['php'] as $arrEachItem){

				if($arrEachItem['type'] == 'Warning'){
					$arrTags['warning_count']++;
				}elseif($arrEachItem['type'] == 'Notice'){
					$arrTags['notice_count']++;
				}else{
					$arrTags['other_count']++;
				}

				$arrTags['errors'] .= $this->resTemplate->build('components/php-error.tpl',$arrEachItem);
			}

			foreach($this->arrDebugLog['Database']['queries'] as $arrEachItem){
				$arrTags['database_queries'] .= $this->resTemplate->build('components/database-query.tpl',$arrEachItem);
			}
			$arrTags['database_query_count'] = count($this->arrDebugLog['Database']['queries']);


			foreach($this->arrDebugLog['View']['usage'] as $arrEachItem){

				if($arrEachItem['instance'] != 'TwistDebugBar'){
					$arrTags['views'] .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>",$arrEachItem['instance'],$arrEachItem['file'],implode("<br>",$arrEachItem['tags']));
				}
			}

			$arrTags['route_current'] = print_r($arrCurrentRoute,true);
			$arrTags['routes'] = '';

			foreach(\Twist::Route()->getAll() as $strType => $arrItems){
				foreach($arrItems as $arrEachRoute){
					$arrEachRoute['highlight'] = ($arrEachRoute['registered_uri'] == $arrCurrentRoute['registered_uri']) ? 'highlight' : '';
					$arrEachRoute['item'] = (is_array($arrEachRoute['item'])) ? implode('->',$arrEachRoute['item']) : $arrEachRoute['item'];
					$arrTags['routes'] .=  $this->resTemplate->build('components/each-route.tpl',$arrEachRoute);
				}
			}

			$arrTags['get'] = print_r($_GET,true);
			$arrTags['post'] = print_r($_POST,true);
			$arrTags['cookie'] = print_r($_COOKIE,true);
			$arrTags['request_headers'] = print_r(Error::apacheRequestHeaders(),true);
			$arrTags['server'] = print_r(Error::serverInformation(),true);

			/**
			 * Process the stats timer bar graph
			 * @todo tidy up masivley and made a function in Timer
			 */

			$intTotalTime = $arrTimer['end']-$arrTimer['start'];
			$intTotalPercentage = 0;

			foreach($arrTimer['log'] as $strKey => $arrInfo){

				$intPercentage = ($arrInfo['time']/$intTotalTime)*100;
				$intTotalPercentage += $intPercentage;

				$arrTimelineTags = array(
					'total_percentage' => round($intTotalPercentage,2),
					'percentage' => round($intPercentage,2),
					'time' => round($arrInfo['time'],4),
					'title' => $strKey
				);

				$arrTags['timeline'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);

				$arrTimelineTags['title'] = $arrInfo['memory'];
				$arrTags['memory_chart'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);
			}

			$arrTimelineTags = array(
				'total_percentage' => 100,
				'percentage' => round(100-$intTotalPercentage,2),
				'time' => round($intTotalTime,4),
				'title' => 'Page Loaded'
			);

			$arrTags['timeline'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);

			$arrTimelineTags['title'] = $arrTimer['memory_end'];
			$arrTags['memory_chart'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);

			$arrTags['execution_time'] = round($intTotalTime,4);

			return $this->resTemplate->build('_base.tpl',$arrTags);
		}

	}