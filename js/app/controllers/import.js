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
			{
				name: 'KeePass CSV',
				value: 'keepassCsv'
			},
			{
				name: 'LastPass CSV',
				value: 'lastpassCsv'
			},
			{
				name: 'Passman CSV',
				value: 'passmanCsv'
			},
			{
				name: 'Passman JSON',
				value: 'passmanJson'
			}
		];
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

		$scope.startImport = function(importerType){
			if(file_data){
				var parsed_data = $window.PassmanImporter[importerType].readFile(file_data);
				console.log('Data parsed!', parsed_data);
			}
		}
	}]);

