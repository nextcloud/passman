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
		var _tags = [
			{text: 'test'},
			{text: 'test 1'},
			{text: 'example'},
		];
		return {
			getTags: function () {
				return _tags;
			},
			searchTag: function(string){
				return $filter('filter')(_tags,{text: string });
			},
			addTags: function (tags) {
				for(var i =0; i < tags.length; i++){
					if(_tags.indexOf(tags[i]) == -1){
						_tags.push(tags[i]);
					}
				}
			},
			removeTag: function (tag) {

			}
		}
	}]);
