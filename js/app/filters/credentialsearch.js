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
	.filter('credentialSearch', function() {
	return function(credentials, tags) {
		var _credentials = [];
		if(tags.length > 0) {
			for (var ci = 0; ci < credentials.length; ci++) {
				var c = credentials[ci];
				_credentials.push(c);
			}
		}
		if(tags.length == 0){
			_credentials = credentials;
		}
		return _credentials;
	};
});