'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:ImportCtrl
 * @description
 * # ImportCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('ImportCtrl', ['$scope', '$window', function ($scope, $window) {
		//@TODo read the available importers from $window.PassmanImporter
		$scope.available_importers = [

		];


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

		$scope.setImporter = function (importer) {
			importer = JSON.parse(importer);
			$scope.selectedImporter = importer;
		};

		var file_data;
		$scope.fileLoaded = function (file) {
			file_data = file.data.split(',');
			file_data = decodeURIComponent(escape(window.atob( file_data[1] ))); //window.atob();
		};

		$scope.fileLoadError = function (file) {
			console.error('Error loading file');
		};
		$scope.fileSelectProgress = function (progress) {

		};

		$scope.startImport = function(){
			if(file_data){
				var parsed_data = $window.PassmanImporter[$scope.selectedImporter.id].readFile(file_data);
				console.log('Data parsed!', parsed_data);
			}
		}
	}]);

