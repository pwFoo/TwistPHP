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

	namespace Packages\manager\Routes;

	use Twist;
	use Twist\Core\Models\Route\Meta;
	use Twist\Core\Routes\Base;

	/**
	 * Manager route file that registers all the routes and restrictions required to allow the Manager to be run.
	 * The manager route can be easily added to your site by calling the Twist::Route()->manager() alias function.
	 * @package string|Twist\Core\Routes
	 */
	class Manager extends Base{

		public function load(){

			\Twist::define('TWIST_MANAGER_PACKAGE',realpath(dirname(__FILE__).'/../'));

			$this->meta()->title('TwistPHP Manager');
			$this->meta()->robots('noindex,nofollow');
			$this->meta()->css(rtrim(\Twist::Route()->baseURI(),'/').'/packages/manager/Resources/css/twistmanager.css',5);
			$this->meta()->js(rtrim(\Twist::Route()->baseURI(),'/').'/packages/manager/Resources/js/twistmanager.js',5);

			//Allow the manager to still be accessible even in maintenance mode
			$this->bypassMaintenanceMode( '/%' );

			$this->setDirectory(realpath(dirname(__FILE__).'/../Views'));

			$this->baseView('_base.tpl');
			$this->controller('/%','Packages\manager\Controllers\Manager');
			$this->controller('/packages/%','Packages\manager\Controllers\Packages');
			$this->controller('/protect/%','Packages\manager\Controllers\Protect');
			$this->controller('/settings/%','Packages\manager\Controllers\Settings');
			$this->controller('/settings/scheduled-tasks/%','Packages\manager\Controllers\Scheduler');

			$this->ajax('/ajax/%','Packages\manager\Controllers\AJAX');
			$this->upload('/upload/{function}/{asset_group}');

			//Load in all any hooks registered to extend the Twist Manager
			$arrRoutes = \Twist::framework()->hooks()->getAll('TWIST_MANAGER_ROUTE');

			if(count($arrRoutes)){
				foreach($arrRoutes as $strEachHook){
					if(file_exists($strEachHook)){
						include $strEachHook;
					}
				}
			}

			//Load in all any hooks registered to extend the Twist Manager
			$arrCSSHooks = \Twist::framework()->hooks()->getAll('TWIST_MANAGER_CSS');

			if(count($arrCSSHooks)){
				foreach($arrCSSHooks as $arrCSSFiles){

					$intOrder = null;
					if(array_key_exists('files',$arrCSSFiles)){

						if(array_key_exists('order',$arrCSSFiles)){
							$intOrder = $arrCSSFiles['order'];
						}

						$arrCSSFiles = $arrCSSFiles['files'];
					}

					foreach($arrCSSFiles as $mxdCSSFile){
						$this->meta()->css($mxdCSSFile,$intOrder);
					}
				}
			}

			//Load in all any hooks registered to extend the Twist Manager
			$arrJSHooks = \Twist::framework()->hooks()->getAll('TWIST_MANAGER_JS');

			if(count($arrJSHooks)){
				foreach($arrJSHooks as $arrJSFiles){

					$intOrder = null;
					if(array_key_exists('files',$arrJSFiles)){

						if(array_key_exists('order',$arrJSFiles)){
							$intOrder = $arrJSFiles['order'];
						}

						$arrJSFiles = $arrJSFiles['files'];
					}

					foreach($arrJSFiles as $mxdJSFile){
						$this->meta()->js($mxdJSFile,$intOrder);
					}
				}
			}

			//Load in all manager access restrictions
			$this->restrictAdmin('/%','/login');
			$this->unrestrict('/authenticate');
			$this->unrestrict('/cookies');
			$this->unrestrict('/forgotten-password');
		}
	}