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
			$scope.$digest();
		};

		$scope.fileLoadError = function (file) {
			console.error('Error loading file');
		};
		$scope.fileSelectProgress = function (progress) {

		};

		var parsed_data;

		$scope.import_progress = {
			progress: 0,
			loaded: 0,
			total: 0
		};
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
			_credential.vault_id = $scope.active_vault.vault_id;
			CredentialService.createCredential(_credential).then(function (result) {
				if(result.credential_id){
					_log('Added  '+ _credential.label);
					if(parsed_data[ parsed_data_index +1]) {
						$scope.import_progress = {
							progress: parsed_data_index / parsed_data.length * 100,
							loaded: parsed_data_index,
							total: parsed_data.length
						};

						addCredential(parsed_data_index +1)
					} else {
						$scope.import_progress =  {
							progress: 100,
							loaded: parsed_data.length,
							total: parsed_data.length
						};
						_log('DONE!');
					}
				}
			})
		};


		$scope.file_read_progress = {
			percent: 0,
			loaded: 0,
			total: 0
		};
		$scope.startImport = function(){
			$scope.import_progress = 0;
			$scope.file_read_percent = 0;
			if(file_data){
				$window.PassmanImporter[$scope.selectedImporter.id]
				.readFile(file_data)
				.then(function(parseddata){
					parsed_data = parseddata;
					$scope.file_read_progress = {
						percent: 100,
						loaded: parsed_data.length,
						total: parsed_data.length
					};
					_log('Parsed '+ parsed_data.length + ' credentials, starting to import');
					if( parsed_data.length > 0){
						addCredential(0);
					} else {
						// @TODO Show message no data found
					}
				}).progress(function(progress){
					$scope.file_read_progress = progress;
					$scope.$digest();
				});
			}
		}

	}]);

