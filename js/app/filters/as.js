(function () {
	'use strict';

	/**
	 * @ngdoc filter
	 * @name passmanApp.filter:as
	 * @function
	 * @description
	 * # as
	 * Filter in the passmanApp.
	 */
	angular.module('passmanApp')
		.filter("as", function ($parse) {
			return function (value, context, path) {
				return $parse(path).assign(context, value);
			};
		});
}());