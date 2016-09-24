'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:SettingsCtrl
 * @description
 * # SettingsCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('SettingsCtrl', ['$scope', '$rootScope', 'SettingsService', 'VaultService', 'CredentialService', '$location', '$routeParams', '$http',
		function ($scope, $rootScope, SettingsService, VaultService, CredentialService, $location, $routeParams, $http) {

			$scope.tabs = [
				{
					title: 'General settings',
					url: 'views/partials/forms/settings/general_settings.html'
				},
				{
					title: 'Password Tool',
					url: 'views/partials/forms/settings/tool.html'

				},
				{
					title: 'Import credentials',
					url: 'views/partials/forms/settings/import.html'

				},
				{
					title: 'Export credentials',
					url: 'views/partials/forms/settings/export.html'

				}];

			$scope.currentTab = $scope.tabs[2];

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
				$scope.active_vault = vault;
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
				VaultService.getVault($scope.active_vault).then(function (credentials) {
					var results = [];
					for (var i = 0; i < credentials.length; i++) {
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

