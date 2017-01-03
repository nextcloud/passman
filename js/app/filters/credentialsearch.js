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
	 * @name passmanApp.filter:selectedTags
	 * @function
	 * @description
	 * # selectedTags
	 * Filter in the passmanApp.
	 */
	angular.module('passmanApp')
		.filter('credentialSearch', function () {
			return function (credentials, filter) {
				var _credentials = [];
				if (credentials) {
					if (!filter) {
						return credentials;
					}
					if (filter.filterText.trim() === "") {
						return credentials;
					}
					var matchedWithFilter = function (c) {
						for (var f = 0; f < filter.fields.length; f++) {
							var field = filter.fields[f];
							var fieldValue = (typeof c[field] === 'string') ? c[field] : JSON.stringify(c[field]);

							if (filter.hasOwnProperty('useRegex') && filter.useRegex === true) {
								try {
									var patt = new RegExp(filter.filterText);
									if (patt.test(fieldValue)) {
										return true;
									}
								} catch (e){
									// Don't catch regex errors.
								}
							}

							if (fieldValue.toLowerCase().indexOf(filter.filterText.toLowerCase()) >= 0) {
								return true;
							}
						}
						return false;
					};

					for (var ci = 0; ci < credentials.length; ci++) {
						var c = credentials[ci];
						if (matchedWithFilter(c)) {
							_credentials.push(c);
						}
					}
					return _credentials;
				} else {
					return [];
				}
			};
		});
}());