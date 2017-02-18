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

				link: function (scope, el) {
					scope.success = scope.success();
					scope.error = scope.error();
					scope.progress = scope.progress();
					var fileReader = new $window.FileReader();
					var _currentFile;

					fileReader.onload = function () {
						_currentFile.data = fileReader.result;
						scope.success(_currentFile);
					};

					fileReader.onprogress = function (event) {
						var percent = (event.loaded / event.total * 100);
						if(scope.progress) {
							scope.$apply(scope.progress({
								file_total: event.total,
								file_loaded: event.loaded,
								file_percent: percent
							}));
						}
					};

					fileReader.onerror = function () {
						scope.error();
					};

					el.bind('change', function (e) {
						var _queueTotalFileSize = 0;
						var i;
						//Calcutate total size
						for (i = 0; i < e.target.files.length; i++) {
							_queueTotalFileSize += e.target.files[i].size;
						}
						//Now load the files
						for (i = 0; i < e.target.files.length; i++) {
							_currentFile = e.target.files[i];
							var mb_limit = 5;
							if (_currentFile.size > (mb_limit * 1024 * 1024)) {
								scope.error('TO_BIG', _currentFile);
							}
							fileReader.readAsDataURL(_currentFile);

						}
					});
				}
			};
		}]);
}());