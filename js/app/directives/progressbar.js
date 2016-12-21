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
		.directive('progressBar', ['$translate', function ($translate) {
			return {
				restrict: 'A',
				template: '' +
				'<div class="progress">' +
				'<div class="progress-bar" role="progressbar" aria-valuenow="{{progress}}"aria-valuemin="0" aria-valuemax="100" style="width:{{progress}}%;" use-theme>' +
				'<span class="sr-only">{{progress}}% {{completed_text}}</span>' +
				'<span ng-if="index && total" class="progress-label" use-theme type="\'color\'" color="\'true\'">{{index}} / {{total}}</span>' +
				'<span ng-if="!index && !total" class="progress-label" use-theme type="\'color\'" color="\'true\'">{{progress}}%</span>' +
				'</div>' +
				'</div>',
				scope: {
					progress: '=progressBar',
					index: '=index',
					total: '=total'
				},

				link: function (scope) {
					$translate(['complete']).then(function (translations) {
						scope.completed_text = translations.complete;
					});
				}
			};
		}]);
}());