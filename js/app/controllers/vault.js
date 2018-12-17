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
	 * @name passmanApp.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('VaultCtrl', ['$scope', 'VaultService', 'SettingsService', 'CredentialService', '$location', 'ShareService', 'EncryptService', '$translate', '$rootScope', '$interval',
			function ($scope, VaultService, SettingsService, CredentialService, $location, ShareService, EncryptService, $translate, $rootScope, $interval) {
			VaultService.getVaults().then(function (vaults) {
				$scope.vaults = vaults;
				if (SettingsService.getSetting('defaultVault') != null) {
					var default_vault = SettingsService.getSetting('defaultVault');

					/**
					 * Using a native for loop for preformance reasons.
					 * More info see http://stackoverflow.com/questions/13843972/angular-js-break-foreach
					 */
					for (var i = 0; i < vaults.length; i++) {
						var vault = vaults[i];
						if (vault.guid === default_vault.guid) {
							$scope.default_vault = true;
							//This prevents the opening of the default vault if the user logs out
							if(!$rootScope.override_default_vault){
                                $scope.list_selected_vault = vault;
                                $rootScope.override_default_vault=false;
							}
							SettingsService.setSetting('defaultVault', vault);
							if (SettingsService.getSetting('defaultVaultPass')) {
								$location.path('/vault/' + vault.guid);
							}
							$scope.vault_tries[vault.guid] = {
								tries: 0,
								timeout: 0
							};
							break;
						}
					}
				}
			});


			var key_strengths = [
				'password.poor',
				'password.poor',
				'password.weak',
				'password.good',
				'password.strong'
			];

			$scope.default_vault = false;
			$scope.remember_vault_password = false;
			$scope.auto_logout_timer = false;
			$scope.logout_timer = '0';
			$scope.list_selected_vault = false;
			$scope.minimal_value_key_strength = 3;

			var settingsLoaded = function () {
				$scope.minimal_value_key_strength = SettingsService.getSetting('vault_key_strength');
				$translate(key_strengths[SettingsService.getSetting('vault_key_strength')]).then(function (translation) {
					$scope.required_score = {'strength': translation};
				});
			};

			if (!SettingsService.getSetting('settings_loaded')) {
				$rootScope.$on('settings_loaded', function () {
					settingsLoaded();
				});
			} else {
				settingsLoaded();
			}

			$scope.toggleDefaultVault = function () {
				$scope.default_vault = !$scope.default_vault;
				if ($scope.default_vault === true) {
					SettingsService.setSetting('defaultVault', $scope.list_selected_vault);
				} else {
					SettingsService.setSetting('defaultVault', null);
				}
			};

			$scope.toggleRememberPassword = function () {
				$scope.remember_vault_password = !$scope.remember_vault_password;
				if ($scope.remember_vault_password) {
					SettingsService.setSetting('defaultVault', $scope.list_selected_vault);
					$scope.default_vault = true;
				}
				if ($scope.remember_vault_password !== true) {
					SettingsService.setSetting('defaultVault', null);
				}
			};

			$scope.toggleAutoLogout = function () {
				$scope.auto_logout_timer = !$scope.auto_logout_timer;
			};

			$scope.clearState = function () {
				$scope.list_selected_vault = false;
				$scope.creating_vault = false;
				$scope.error = false;
			};

			$scope.selectVault = function (vault) {
				$scope.list_selected_vault = vault;
				if(!$scope.vault_tries[vault.guid]) {
					$scope.vault_tries[vault.guid] = {
						tries: 0,
						timeout: 0
					};
				}
			};
			$scope.sharing_keys = {};
			$scope.newVault = function () {
				$scope.creating_vault = true;
				var key_size = 1024;
				ShareService.generateRSAKeys(key_size).progress(function (progress) {
					var p = progress > 0 ? 2 : 1;
					var msg = $translate.instant('generating.sharing.keys');
					msg = msg.replace('%step', p);
					$scope.creating_keys = msg;
					$scope.$digest();
				}).then(function (kp) {
					var pem = ShareService.rsaKeyPairToPEM(kp);
					$scope.creating_keys = false;
					$scope.sharing_keys.private_sharing_key = pem.privateKey;
					$scope.sharing_keys.public_sharing_key = pem.publicKey;
					$scope.$digest();
				});

			};

			$scope.requestDeletion = function (vault) {
				$location.path('/vault/' + vault.guid +'/request-deletion');
			};

			var _loginToVault = function (vault, vault_key) {
				var _vault = angular.copy(vault);
				_vault.vaultKey = angular.copy(vault_key);
				delete _vault.credentials;
				var timer = parseInt($scope.logout_timer);
				if ($scope.auto_logout_timer && timer > 0) {
					$rootScope.$broadcast('logout_timer_set', timer * 60);
				}

				VaultService.setActiveVault(_vault);
				$location.path('/vault/' + vault.guid);
			};

			$scope.selectLogoutTimer = function (time) {
				$scope.auto_logout_timer = true;
				$scope.logout_timer = time;
			};

			var tickLockTimer = function (guid) {
				$scope.vault_tries[guid].timeout = $scope.vault_tries[guid].timeout - 1;
				if($scope.vault_tries[guid].timeout <= 0){
					$interval.cancel($scope.vault_tries[guid].timer);
					$scope.vault_tries[guid].timeout = 0;
				}
			};

			$scope.vault_tries = {};

			$scope.vaultDecryptionKey = '';
			$scope.loginToVault = function (vault, vault_key) {
				$scope.error = false;
				var _vault = angular.copy(vault);
				_vault.vaultKey = angular.copy(vault_key);

				VaultService.setActiveVault(_vault);
				try {
					EncryptService.decryptString(vault.challenge_password);
					if ($scope.remember_vault_password) {
						SettingsService.setSetting('defaultVaultPass', vault_key);
					}
					_loginToVault(vault, vault_key);

				} catch (e) {
					$scope.error = $translate.instant('invalid.vault.key');

					$scope.vault_tries[vault.guid].tries = $scope.vault_tries[vault.guid].tries + 1;

					if($scope.vault_tries[vault.guid].tries >= 3){
						var duration = (Math.pow(2, 1 / 7) * Math.pow(15, 4 / 7)) * Math.pow((Math.pow(2, 2 / 7) * Math.pow(15, 1 / 7)), $scope.vault_tries[vault.guid].tries);
						$scope.vault_tries[vault.guid].timeout = duration;

						if($scope.vault_tries[vault.guid].hasOwnProperty('timer')){
							$interval.cancel($scope.vault_tries[vault.guid].timer);
						}

						$scope.vault_tries[vault.guid].timer = $interval(function () {
							tickLockTimer(vault.guid);
						} ,1000);
					}

				}
			};



                $scope.createVault = function (vault_name, vault_key, vault_key2) {
				if (vault_key !== vault_key2) {
					$scope.error = $translate.instant('password.do.not.match');
					return;
				}
				VaultService.createVault(vault_name).then(function (vault) {
					$scope.vaults.push(vault);
					var _vault = angular.copy(vault);
					_vault.vaultKey = angular.copy(vault_key);
					VaultService.setActiveVault(_vault);
					SettingsService.setSetting('defaultVaultPass', null);
					SettingsService.setSetting('defaultVault', null);
					var test_credential = CredentialService.newCredential();
					test_credential.label = 'Test key for vault ' + vault_name;
					test_credential.hidden = true;
					test_credential.vault_id = vault.vault_id;
					test_credential.password = 'lorum ipsum';
					CredentialService.createCredential(test_credential).then(function () {
						_vault.public_sharing_key = angular.copy($scope.sharing_keys.public_sharing_key);
						_vault.private_sharing_key = EncryptService.encryptString(angular.copy($scope.sharing_keys.private_sharing_key));
						VaultService.updateSharingKeys(_vault).then(function () {
							_loginToVault(vault, vault_key);
						});
					});
				});
			};
		}]);
}());