(function () {
	'use strict';

	/**
	 * @ngdoc function
	 * @name passmanApp.controller:ImportCtrl
	 * @description
	 * # ImportCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('ExportCtrl', ['$scope', '$window', 'CredentialService', 'VaultService', function ($scope, $window, CredentialService, VaultService) {
			$scope.available_exporters = [];
			$scope.active_vault = VaultService.getActiveVault();


			$scope.$watch(function () {
				return $window.PassmanExporter;
			}, function (exporters) {
				for (var key in exporters) {

					var exporter = exporters[key];
					if (exporter.hasOwnProperty('info')) {
						$scope.available_exporters.push(exporter.info);
					}
				}
			}, true);
			$scope.log = [];
			$scope.setExporter = function (exporter) {
				exporter = JSON.parse(exporter);
				$scope.selectedExporter = exporter;
			};
			var _log = function (str) {
				$scope.log.push(str);
			};


			$scope.startExport = function () {
				_log('Starting export');
				var _credentials = [];
				VaultService.getVault(VaultService.getActiveVault()).then(function (vault) {
					_log('Decrypting credentials');
					if(vault.hasOwnProperty('credentials')){
						if(vault.credentials.length > 0){
							for(var i =0; i < vault.credentials.length; i++){
								var _credential = angular.copy(vault.credentials[i]);
								if(_credential.hidden === 0){
									_credential = CredentialService.decryptCredential(_credential);
									_credentials.push(_credential);
								}
							}
							$window.PassmanExporter[$scope.selectedExporter.id].export(_credentials).then(function () {
								_log('Done');
							});
						}

					}
				});
			};



		}]);

}());