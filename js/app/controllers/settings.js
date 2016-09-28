'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:SettingsCtrl
 * @description
 * # SettingsCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('SettingsCtrl', ['$scope', '$rootScope', 'SettingsService', 'VaultService', 'CredentialService', '$location', '$routeParams', '$http', 'EncryptService',
		function ($scope, $rootScope, SettingsService, VaultService, CredentialService, $location, $routeParams, $http, EncryptService) {

			if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
				if (!$scope.active_vault) {
					$location.path('/')
				}
			} else {
				if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
					var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
					VaultService.setActiveVault(_vault);
					$scope.active_vault = _vault;

				}
			}

			var _settings = {
				pwSettings: {
					'length': 12,
					'useUppercase': true,
					'useLowercase': true,
					'useDigits': true,
					'useSpecialChars': true,
					'minimumDigitCount': 3,
					'avoidAmbiguousCharacters': false,
					'requireEveryCharType': true,
					'generateOnCreate': true,
				}
			};

			$scope.vault_settings = angular.merge(_settings, $scope.active_vault.vault_settings);
			console.log($scope.vault_settings);


			$scope.saveVaultSettings = function () {
				var _vault = $scope.active_vault;
				_vault.vault_settings = angular.copy($scope.vault_settings);
				_vault.vault_settings = window.btoa(JSON.stringify(_vault.vault_settings));
				VaultService.updateVault(_vault).then(function () {
					VaultService.setActiveVault(_vault);
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
				return tab.url == $scope.currentTab.url;
			};

			var getPassmanVersion = function () {
				var url = OC.generateUrl('apps/passman/api/internal/version');
				$http.get(url).then(function (result) {
					$scope.passman_version = result.data.version;
				})
			};
			getPassmanVersion();

			$scope.$watch(function () {
				return VaultService.getActiveVault()
			}, function (vault) {
				if (vault) {
					$scope.active_vault = vault;
				}
			});

			if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
				if (!$scope.active_vault) {
					$location.path('/')
				}
			} else {
				if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
					var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
					_vault.vaultKey = angular.copy(SettingsService.getSetting('defaultVaultPass'));
					VaultService.setActiveVault(_vault);
					$scope.active_vault = _vault;

				}
			}
			if ($scope.active_vault) {
				$scope.$parent.selectedVault = true;
			}

			$rootScope.$on('logout', function () {
				$scope.selectedVault = false;
			});

			$scope.startScan = function (minStrength) {
				VaultService.getVault($scope.active_vault).then(function (vault) {
					var results = [];
					for (var i = 0; i < vault.credentials.length; i++) {
						var c = CredentialService.decryptCredential(angular.copy(credentials[i]));
						if (c.password && c.password.length > 0 && c.hidden == 0) {
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
						//@todo loop custom fields (if any and check secret fields
					}
					$scope.scan_result = results;
				});
			};

			$scope.cancel = function () {
				$location.path('/vault/' + $routeParams.vault_id);
			};

		}]);

