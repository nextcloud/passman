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
	 * @ngdoc service
	 * @name passmanApp.TagService
	 * @description
	 * # TagService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('TagService', ['$filter', function ($filter) {
			var _tags = [];
			return {
				getTags: function () {
					return _tags;
				},
				searchTag: function (string) {
					return $filter('filter')(_tags, {text: string});
				},
				addTags: function (tags) {
					for (var i = 0; i < tags.length; i++) {
						if (tags[i].text) {
							if ($filter('filter')(_tags, {text: tags[i].text}).length === 0) {
								_tags.push(tags[i]);
							}
						}
					}
				},
				resetTags: function () {
					_tags = [];
				}

			};
		}]);
}());