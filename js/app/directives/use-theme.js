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
		.directive('useTheme', [function () {

			function invertColor (hexTripletColor) {
				var color = hexTripletColor;
				color = color.substring(1);           // remove #
				color = parseInt(color, 16);          // convert to integer
				color = 0xFFFFFF ^ color;             // invert three bytes
				color = color.toString(16);           // convert to hex
				color = ("000000" + color).slice(-6); // pad with leading zeros
				color = "#" + color;                  // prepend #
				return color;
			}

			return {
				restrict: 'A',
				scope: {
					type: '=type',
					color: '=',
					negative: '='
				},
				link: function (scope, el) {
					var _color = jQuery('#header').css('background-color');
					var _bg = _color;
					if (scope.negative) {
						_bg = invertColor(_bg);
					}
					if (!scope.type) {
						jQuery(el).css('background-color', _bg);
					} else {
						jQuery(el).css(scope.type, _bg);
					}
					if (scope.color) {
						jQuery(el).css('color', invertColor(_color));
					}
				}
			};
		}]);
}());