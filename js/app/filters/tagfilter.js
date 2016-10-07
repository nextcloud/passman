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