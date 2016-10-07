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
						if ($filter('filter')(_tags, {text: tags[i].text}).length === 0) {
							_tags.push(tags[i]);
						}
					}
				},
				removeTag: function (tag) {

				}
			};
		}]);
}());