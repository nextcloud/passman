'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:ImportCtrl
 * @description
 * # ImportCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('ImportCtrl', ['$scope', '$window', 'CredentialService', 'VaultService', function ($scope, $window, CredentialService, VaultService) {
		//@TODo read the available importers from $window.PassmanImporter
		$scope.available_importers = [

		];
		$scope.active_vault = VaultService.getActiveVault();


		$scope.$watch(function(){
			return $window.PassmanImporter;
		}, function (importers) {
			for(var key in importers){
				var importer = importers[key];
				if(importer.hasOwnProperty('info')){
					$scope.available_importers.push(importer.info);
				}
			}
		}, true);
		$scope.log = [];
		$scope.setImporter = function (importer) {
			importer = JSON.parse(importer);
			$scope.selectedImporter = importer;
		};
		var _log = function(str){
			$scope.log.push(str);
		};

		var file_data;
		$scope.fileLoaded = function (file) {
			file_data = file.data.split(',');
			file_data = decodeURIComponent(escape(window.atob( file_data[1] ))); //window.atob();
			_log('File read successfully!')
			$scope.$apply();
		};

		$scope.fileLoadError = function (file) {
			console.error('Error loading file');
		};
		$scope.fileSelectProgress = function (progress) {

		};

		var parsed_data;
		$scope.current_import_index = 0;
		$scope.current_import_length = 0;
		var addCredential = function(parsed_data_index){
			if(!parsed_data[parsed_data_index]){
				return;
			}
			var _credential = parsed_data[parsed_data_index];
			if(!_credential.label){
				if(parsed_data[ parsed_data_index +1]) {
					_log('Credential has no label, skipping');
					addCredential(parsed_data_index +1)
				}
				return
			}
			_log('Adding  '+ _credential.label);
			$scope.current_import_index = parsed_data_index;
			_credential.vault_id = $scope.active_vault.vault_id;
			CredentialService.createCredential(_credential).then(function (result) {
				if(result.credential_id){
					_log('Added  '+ _credential.label);
					if(parsed_data[ parsed_data_index +1]) {
						addCredential(parsed_data_index +1)
					} else {
						_log('DONE!');
					}
				}
			})
		};

		$scope.startImport = function(){
			if(file_data){
				parsed_data = $window.PassmanImporter[$scope.selectedImporter.id].readFile(file_data);
				_log('Parsed '+ parsed_data.length + ' credentials, starting to import');
				$scope.current_import_length = parsed_data.length;
				if( parsed_data.length > 0){
					addCredential(0);
				} else {
					// @TODO Show message no data found
				}

			}
		}

	}]);

