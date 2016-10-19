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
	 * @ngdoc filter
	 * @name passmanApp.filter:propsFilter
	 * @function
	 * @description
	 * # propsFilter
	 * Filter in the passmanApp.
	 */
	angular.module('passmanApp')
		.filter('propsFilter', function () {
			return function (items, props) {
				var out = [];

				if (angular.isArray(items)) {
					var keys = Object.keys(props);

					items.forEach(function (item) {
						var itemMatches = false;

						for (var i = 0; i < keys.length; i++) {
							var prop = keys[i];
							var text = props[prop].toLowerCase();
							if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
								itemMatches = true;
								break;
							}
						}

						if (itemMatches) {
							out.push(item);
						}
					});
				} else {
					// Let the output be the input untouched
					out = items;
				}

				return out;
			};
		});
}());