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
		.controller('SettingsCtrl', ['$scope', '$rootScope', 'SettingsService', 'VaultService', 'CredentialService', '$location', '$routeParams', '$http', 'EncryptService', 'NotificationService', '$sce', '$translate',
			function ($scope, $rootScope, SettingsService, VaultService, CredentialService, $location, $routeParams, $http, EncryptService, NotificationService, $sce, $translate) {
				$scope.vault_settings = {};
				$scope.new_vault_name = '';
				$scope.showGenericImport = false;

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
					if (!$scope.vault_settings.hasOwnProperty('pwSettings')) {
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

				var key_strengths = [
					'password.poor',
					'password.poor',
					'password.weak',
					'password.good',
					'password.strong'
				];

				$scope.minimal_value_key_strength = SettingsService.getSetting('vault_key_strength');
				$translate(key_strengths[SettingsService.getSetting('vault_key_strength')]).then(function (translation) {
					$scope.required_score = {'strength': translation};
				});

				var btn_txt = $translate.instant('bookmarklet.text');
				var http = location.protocol, slashes = http.concat("//"), host = slashes.concat(window.location.hostname), complete = host + location.pathname;
				$scope.bookmarklet = $sce.trustAsHtml("<a class=\"button\" href=\"javascript:(function(){var a=window,b=document,c=encodeURIComponent,e=c(document.title),d=a.open('" + complete + "bookmarklet?url='+c(b.location)+'&title='+e,'bkmk_popup','left='+((a.screenX||a.screenLeft)+10)+',top='+((a.screenY||a.screenTop)+10)+',height=750px,width=475px,resizable=0,alwaysRaised=1');a.setTimeout(function(){d.focus()},300);})();\">" + btn_txt + "</a>");


				$scope.saveVaultSettings = function () {
					var _vault = $scope.active_vault;
					_vault.name = $scope.new_vault_name;
					_vault.vault_settings = angular.copy($scope.vault_settings);
					VaultService.updateVault(_vault).then(function () {
						//VaultService.setActiveVault(_vault);
						$scope.active_vault.name = angular.copy(_vault.name);
						NotificationService.showNotification($translate.instant('settings.saved'), 5000);
					});
				};


				$scope.tabs = [
					{
						title: $translate.instant('settings.general'),
						url: 'views/partials/forms/settings/general_settings.html'
					},
					{
						title: $translate.instant('settings.audit'),
						url: 'views/partials/forms/settings/tool.html'

					},
					{
						title: $translate.instant('settings.password'),
						url: 'views/partials/forms/settings/password_settings.html'

					},
					{
						title: $translate.instant('settings.import'),
						url: 'views/partials/forms/settings/import.html'

					},
					{
						title: $translate.instant('settings.export'),
						url: 'views/partials/forms/settings/export.html'

					},
					{
						title: $translate.instant('settings.sharing'),
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

				var getCurrentVaultCredentials = function (callback) {
					VaultService.getVault($scope.active_vault).then(callback);
				};

				$scope.startScan = function (minStrength) {
					getCurrentVaultCredentials(function (vault) {
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
												guid: c.guid,
												label: c.label,
												password: c.password,
												password_zxcvbn_result: zxcvbn_result
											});
										}
									}
								} catch (e) {
									console.warn(e);
								}

							}
							//@todo loop custom fields (if any and check secret fields
						}
						$scope.scan_result = results;
					});
				};


				$scope.cur_state = {};


				$scope.$on("$locationChangeStart", function (event) {
					if ($scope.change_pw) {
						if ($scope.change_pw.total > 0 && $scope.change_pw.done < $scope.change_pw.total) {
							if (!confirm($translate.instant('changepw.navigate.away.warning'))) {
								event.preventDefault();
							}
						}
					}
				});


				$scope.changeVaultPassword = function (oldVaultPass, newVaultPass, newVaultPass2) {
					$scope.error = '';
					if (oldVaultPass !== VaultService.getActiveVault().vaultKey) {
						$scope.error = $translate.instant('incorrect.password');
						return;
					}
					if (newVaultPass !== newVaultPass2) {
						$scope.error = $translate.instant('password.no.match');
						return;
					}
					SettingsService.setSetting('defaultVault', null);
					SettingsService.setSetting('defaultVaultPass', null);
					VaultService.getVault($scope.active_vault).then(function (vault) {
						jQuery('input').attr('disabled', true);
						jQuery('button').attr('disabled', true);
						var _selected_credentials = angular.copy(vault.credentials);
						$scope.change_pw = {
							percent: 0,
							done: 0,
							total: _selected_credentials.length
						};
						var changeCredential = function (index, oldVaultPass, newVaultPass) {
							var usedKey = oldVaultPass;

							if (_selected_credentials[index].hasOwnProperty('shared_key')) {
								if (_selected_credentials[index].shared_key) {
									usedKey = EncryptService.decryptString(angular.copy(_selected_credentials[index].shared_key), oldVaultPass);
								}
							}

							CredentialService.reencryptCredential(_selected_credentials[index].guid, usedKey, newVaultPass).progress(function (data) {
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
										NotificationService.showNotification($translate.instant('login.new.pass'), 5000);
									});
								}
							});
						};
						changeCredential(0, VaultService.getActiveVault().vaultKey, newVaultPass);

					});
				};

				$scope.confirm_vault_delete = false;
				$scope.delete_vault_password = '';
        $scope.delete_vault = function() {
          if ($scope.confirm_vault_delete && $scope.delete_vault_password === VaultService.getActiveVault().vaultKey) {
            getCurrentVaultCredentials(function(vault) {
              var credentials = vault.credentials;
              $scope.remove_pw = {
                percent: 0,
                done: 0,
                total: vault.credentials.length,
              };
              var deleteCredential = function(index) {
                $scope.translationData = {
                  password: credentials[index].label,
                };
                CredentialService.destroyCredential(credentials[index].guid).then(function() {
                  var percent = index / vault.credentials.length * 100;
                  $scope.remove_pw = {
                    percent: percent,
                    done: index,
                    total: vault.credentials.length,
                  };
                  if (index === credentials.length - 1) {
                    VaultService.deleteVault(vault).then(function() {
                      SettingsService.setSetting('defaultVaultPass', false);
                      SettingsService.setSetting('defaultVault', null);
                      $rootScope.$broadcast('logout');
                      $location.path('/');
                    });
                    return;
                  }
                  deleteCredential(index + 1);
                });
              };
              deleteCredential(0);
            });
          }

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