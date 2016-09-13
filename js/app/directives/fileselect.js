'use strict';

/**
 * @ngdoc directive
 * @name passmanApp.directive:passwordGen
 * @description
 * # passwordGen
 */
angular.module('passmanApp')
	.directive('fileSelect', ['$window', function ($window) {
		return {
			restrict: 'A',
			scope: {
				success: '&success',
				error: '&error',
				progress: '&progress'
			},

			link: function (scope, el, attr, ctrl) {
				scope.success = scope.success();
				scope.error = scope.error();
				scope.progress = scope.progress();
				var fileReader = new $window.FileReader();
				var _currentFile;

				fileReader.onload = function () {
					_currentFile.data = fileReader.result;
					scope.success(_currentFile)
				};

				fileReader.onprogress = function (event) {
					var percent = (event.loaded / event.total * 100);
					scope.$apply(scope.progress({
						file_total: event.total,
						file_loaded: event.loaded,
						file_percent: percent
					}));
				};

				fileReader.onerror = function () {
					scope.error()
				};

				el.bind('change', function (e) {
					var _queueTotalFileSize = 0;
					var _queueProgressBytes = 0;

					//Calcutate total size
					for (var i = 0; i < e.target.files.length; i++) {
						_queueTotalFileSize += e.target.files[i].size;
					}
					//Now load the files
					for (var i = 0; i < e.target.files.length; i++) {
						_currentFile = e.target.files[i];
						var mb_limit = 2000; //@TODO remove this after test (Set to 2mb)
						if (_currentFile.size > (mb_limit * 1024 * 1024)) {
							scope.error('TO_BIG', _currentFile);
						}
						fileReader.readAsDataURL(_currentFile);

					}
				});
			}
		};
	}]);
