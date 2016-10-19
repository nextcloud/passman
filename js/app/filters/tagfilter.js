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
		.filter('tagFilter', function () {
			return function (credentials, tags) {
				var _credentials = [];
				if (tags.length > 0) {
					for (var ci = 0; ci < credentials.length; ci++) {
						var c = credentials[ci];
						var matches = 0;
						for (var ct = 0; ct < c.tags_raw.length; ct++) {
							var t = c.tags_raw[ct];
							if (tags.indexOf(t.text) !== -1) {
								matches++;
							}
						}
						if (matches === tags.length) {
							_credentials.push(c);
						}
					}
				}
				if (tags.length === 0) {
					_credentials = credentials;
				}
				return _credentials;
			};
		});
}());