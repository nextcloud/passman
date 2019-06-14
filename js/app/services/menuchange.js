/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2019, Felix Nüsse (passman@felixnuesse.de)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function () {
	'use strict';

	/**
	 * @ngdoc service
	 * @name passmanApp.MenuChangeService
	 * @description
	 * # MenuChangeService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('MenuChangeService', [function () {


			return {
				initChangeListener: function ($rootScope, defaultView) {
					var rs=$rootScope;
					$rootScope.$on('$locationChangeStart', function(event, next, current) {
						var regex_uuid="[0-9a-fA-F]{8}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{12}";
						var settings_regex=new RegExp("(.*)apps/passman/#/vault/"+regex_uuid+"/settings");
						var vault_regex=new RegExp("(.*)apps/passman/#/vault/"+regex_uuid);

						if(vault_regex.test(next)){
							console.log("vault_regex!");
							rs.menulocation="CredentialCtrl";
						}

						if(settings_regex.test(next)){
							console.log("Settings!");
							rs.menulocation="SettingsCtrl";
							rs.settingsShown = false;
						}

					});
					rs.menulocation=defaultView;
				},
			};
			}]);
}());
