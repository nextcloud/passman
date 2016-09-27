'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('VaultCtrl', ['$scope', 'VaultService', 'SettingsService', 'CredentialService', '$location', 'ShareService', 'EncryptService', function ($scope, VaultService, SettingsService, CredentialService, $location, ShareService, EncryptService) {
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
					if (vault.guid == default_vault.guid) {
						$scope.default_vault = true;
						$scope.list_selected_vault = vault;
						SettingsService.setSetting('defaultVault', vault);
						if (SettingsService.getSetting('defaultVaultPass')) {
							$location.path('/vault/' + vault.vault_id);
						}
						break;
					}
				}
			}
		});


		$scope.default_vault = false;
		$scope.remember_vault_password = false;
		$scope.list_selected_vault = false;

		$scope.toggleDefaultVault = function () {
			$scope.default_vault = !$scope.default_vault;
			if ($scope.default_vault == true) {
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
			if ($scope.remember_vault_password != true) {
				SettingsService.setSetting('defaultVault', null);
			}
		};

		$scope.clearState = function () {
			$scope.list_selected_vault = false;
			$scope.creating_vault = false;
			$scope.error = false;
		};

		$scope.selectVault = function (vault) {
			$scope.list_selected_vault = vault;
		};
		$scope.sharing_keys = {};
		$scope.newVault = function () {
			$scope.creating_vault = true;
			var _vault = {};
			var key_size = 1024;
			ShareService.generateRSAKeys(key_size).progress(function (progress) {
				var p = progress > 0 ? 2 : 1;
				$scope.creating_keys = 'Generating sharing keys (' + p + ' / 2)';
				$scope.$apply();
			}).then(function (kp) {
				var pem = ShareService.rsaKeyPairToPEM(kp);
				$scope.creating_keys = false;
				$scope.sharing_keys.private_sharing_key = pem.privateKey;
				$scope.sharing_keys.public_sharing_key = pem.publicKey;
				$scope.$apply();
			});

		};

		var _loginToVault = function (vault, vault_key) {
			var _vault = angular.copy(vault);
			_vault.vaultKey = angular.copy(vault_key);
			VaultService.setActiveVault(_vault);
			$location.path('/vault/' + vault.vault_id);
		};

		$scope.vaultDecryptionKey = '';
		$scope.loginToVault = function (vault, vault_key) {
			$scope.error = false;
			var _vault = angular.copy(vault);
			_vault.vaultKey = angular.copy(vault_key);
			VaultService.setActiveVault(_vault);
			VaultService.getVault(vault).then(function (credentials) {
				var credential = credentials[0];
				try {
					var c = CredentialService.decryptCredential(credential);
					if ($scope.remember_vault_password) {
						SettingsService.setSetting('defaultVaultPass', vault_key);
					}
					_loginToVault(vault, vault_key);

				} catch (e) {
					$scope.error = 'Incorrect vault password!'
				}
			})
		};


		$scope.createVault = function (vault_name, vault_key, vault_key2) {
			if (vault_key != vault_key2) {
				$scope.error = 'Passwords do not match';
				return;
			}
			VaultService.createVault(vault_name).then(function (vault) {
				$scope.vaults.push(vault);
				var _vault = angular.copy(vault);
				_vault.vaultKey = angular.copy(vault_key);
				VaultService.setActiveVault(_vault);
				var test_credential = CredentialService.newCredential();
				test_credential.label = 'Test key for vault ' + vault_name;
				test_credential.hidden = true;
				test_credential.vault_id = vault.vault_id;
				test_credential.password = 'lorum ipsum';
				CredentialService.createCredential(test_credential).then(function (result) {
					_vault.public_sharing_key = angular.copy($scope.sharing_keys.public_sharing_key);
					_vault.private_sharing_key = EncryptService.encryptString(angular.copy($scope.sharing_keys.private_sharing_key));
					VaultService.updateSharingKeys(_vault).then(function (result) {
						_loginToVault(vault, vault_key);
					})
				})
			});
		};
	}]);
