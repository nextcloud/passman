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
	.directive('colorFromString', [function () {
		return {
			restrict: 'A',
			scope:{
				string: '=colorFromString'
			},
			link: function (scope, el) {
				function genColor(str) { // java String#hashCode
					var hash = 0;
					for (var i = 0; i < str.length; i++) {
						hash = str.charCodeAt(i) + ((hash << 5) - hash);
					}
					var c = (hash & 0x00FFFFFF)
						.toString(16)
						.toUpperCase();

					return '#' + '00000'.substring(0, 6 - c.length) + c;
				}
				scope.$watch('string', function(){
					jQuery(el).css('border-color', genColor(scope.string));
				});

			}
		};
	}]);
}());