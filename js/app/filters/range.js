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
		.filter('range', function () {
			return function (val, range) {
				range = parseInt(range);
				for (var i = 0; i < range; i++)
					val.push(i);
				return val;
			};
		});
}());