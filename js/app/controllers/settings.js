/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
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
	 * @ngdoc function
	 * @name passmanApp.controller:SettingsCtrl
	 * @description
	 * # SettingsCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('SettingsCtrl', ['$scope', '$rootScope', 'SettingsService', 'VaultService', 'CredentialService', '$location', '$routeParams', '$http', 'EncryptService', 'NotificationService', '$sce',
			function ($scope, $rootScope, SettingsService, VaultService, CredentialService, $location, $routeParams, $http, EncryptService, NotificationService, $sce) {
				$scope.vault_settings = {};
				$scope.new_vault_name = '';
				$scope.active_vault = VaultService.getActiveVault();
				if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
					if (!$scope.active_vault) {
						$location.path('/');
						return;
					}
				} else {
					if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
						var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
						_vault.vaultKey = SettingsService.getSetting('defaultVaultPass');
						VaultService.setActiveVault(_vault);
						$scope.active_vault = _vault;
					}
				}

				VaultService.getVault($scope.active_vault).then(function (vault) {
					vault.vaultKey = VaultService.getActiveVault().vaultKey;
					delete vault.credentials;
					VaultService.setActiveVault(vault);
					$scope.vault_settings = vault.vault_settings;
					if(!$scope.vault_settings.hasOwnProperty('pwSettings')){
						$scope.vault_settings.pwSettings = {
							'length': 12,
							'useUppercase': true,
							'useLowercase': true,
							'useDigits': true,
							'useSpecialChars': true,
							'minimumDigitCount': 3,
							'avoidAmbiguousCharacters': false,
							'requireEveryCharType': true,
							'generateOnCreate': true
						};
					}
				});



				var http = location.protocol, slashes = http.concat("//"), host = slashes.concat(window.location.hostname), complete = host + location.pathname;
				$scope.bookmarklet = $sce.trustAsHtml("<a class=\"button\" href=\"javascript:(function(){var a=window,b=document,c=encodeURIComponent,e=c(document.title),d=a.open('" + complete + "bookmarklet?url='+c(b.location)+'&title='+e,'bkmk_popup','left='+((a.screenX||a.screenLeft)+10)+',top='+((a.screenY||a.screenTop)+10)+',height=750px,width=475px,resizable=0,alwaysRaised=1');a.setTimeout(function(){d.focus()},300);})();\">Save in passman</a>");


				$scope.saveVaultSettings = function () {
					var _vault = $scope.active_vault;
					_vault.name = $scope.new_vault_name;
					_vault.vault_settings = angular.copy($scope.vault_settings);
					VaultService.updateVault(_vault).then(function () {
						//VaultService.setActiveVault(_vault);
						$scope.active_vault.name = angular.copy(_vault.name);
						NotificationService.showNotification('Settings saved', 5000);
					});
				};


				$scope.tabs = [
					{
						title: 'General settings',
						url: 'views/partials/forms/settings/general_settings.html'
					},
					{
						title: 'Password Audit',
						url: 'views/partials/forms/settings/tool.html'

					},
					{
						title: 'Password settings',
						url: 'views/partials/forms/settings/password_settings.html'

					},
					{
						title: 'Import credentials',
						url: 'views/partials/forms/settings/import.html'

					},
					{
						title: 'Export credentials',
						url: 'views/partials/forms/settings/export.html'

					},
					{
						title: 'Sharing',
						url: 'views/partials/forms/settings/sharing.html'
					}
				];

				$scope.currentTab = $scope.tabs[0];

				$scope.onClickTab = function (tab) {
					$scope.currentTab = tab;
				};

				$scope.isActiveTab = function (tab) {
					return tab.url === $scope.currentTab.url;
				};

				var getPassmanVersion = function () {
					var url = OC.generateUrl('apps/passman/api/internal/version');
					$http.get(url).then(function (result) {
						$scope.passman_version = result.data.version;
					});
				};
				getPassmanVersion();

				$scope.$watch(function () {
					return VaultService.getActiveVault();
				}, function (vault) {
					if (vault) {
						$scope.active_vault = vault;
					}
				});

				$rootScope.$on('logout', function () {
					$scope.selectedVault = false;
				});
				$scope.startScan = function (minStrength) {
					VaultService.getVault($scope.active_vault).then(function (vault) {
						var results = [];
						for (var i = 0; i < vault.credentials.length; i++) {
							var c = angular.copy(vault.credentials[i]);
							if (c.password && c.hidden === 0) {
								try {
									c = CredentialService.decryptCredential(c);
									if (c.password) {
										var zxcvbn_result = zxcvbn(c.password);
										if (zxcvbn_result.score <= minStrength) {
											results.push({
												credential_id: c.credential_id,
												label: c.label,
												password: c.password,
												password_zxcvbn_result: zxcvbn_result
											});
										}
									}
								} catch (e){
									console.warn(e);
								}

							}
							//@todo loop custom fields (if any and check secret fields
						}
						$scope.scan_result = results;
					});
				};


				$scope.cur_state = {};


				$scope.$on("$locationChangeStart", function(event) {
					if($scope.change_pw){
						if($scope.change_pw.total > 0 && $scope.change_pw.done < $scope.change_pw.total){
							if(!confirm("Are you sure you want to leave?\nThis will corrupt all your credentials")){
								event.preventDefault();
							}
						}
					}
				});


				$scope.changeVaultPassword = function (oldVaultPass, newVaultPass, newVaultPass2) {
					if (oldVaultPass !== VaultService.getActiveVault().vaultKey) {
						$scope.error = 'Your old password is incorrect!';
						return;
					}
					if (newVaultPass !== newVaultPass2) {
						$scope.error = 'New passwords do not match!';
						return;
					}
					VaultService.getVault($scope.active_vault).then(function (vault) {
						var _selected_credentials = [];
						if (vault.credentials.length === 0) {
							$location.path('/');
						}
						for (var i = 0; i < vault.credentials.length; i++) {
							var _credential = vault.credentials[i];
							if (_credential.shared_key === null || _credential.shared_key === '') {
								_selected_credentials.push(_credential);
							}
						}
						$scope.change_pw = {
							percent: 0,
							done: 0,
							total: _selected_credentials.length
						};
						var changeCredential = function (index, oldVaultPass, newVaultPass) {
							CredentialService.reencryptCredential(_selected_credentials[index].guid, oldVaultPass, newVaultPass).progress(function (data) {
								$scope.cur_state = data;
							}).then(function () {
								var percent = index / _selected_credentials.length * 100;
								$scope.change_pw = {
									percent: percent,
									done: index + 1,
									total: _selected_credentials.length
								};
								if (index < _selected_credentials.length - 1) {
									changeCredential(index + 1, oldVaultPass, newVaultPass);
								} else {
									vault.private_sharing_key = EncryptService.decryptString(angular.copy(vault.private_sharing_key), oldVaultPass);
									vault.private_sharing_key = EncryptService.encryptString(vault.private_sharing_key, newVaultPass);
									VaultService.updateSharingKeys(vault).then(function () {
										$rootScope.$broadcast('logout');
										NotificationService.showNotification('Please login with your new vault password', 5000);
									});
								}
							});
						};
						changeCredential(0, VaultService.getActiveVault().vaultKey, newVaultPass);

					});
				};

				$rootScope.$on('logout', function () {
					$scope.active_vault = null;
					VaultService.setActiveVault(null);
					$location.path('/');

				});

				$scope.cancel = function () {
					$location.path('/vault/' + $routeParams.vault_id);
				};

			}]);

}());