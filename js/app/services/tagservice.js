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
		var tags = [
			{text: 'test'},
			{text: 'test 1'},
			{text: 'example'},
		];
		return {
			getTags: function () {
				return tags;
			},
			searchTag: function(string){
				console.log(string)
				return $filter('filter')(tags,{text: string });
			},
			addTags: function (tags) {

			},
			removeTag: function (tag) {

			}
		}
	}]);
