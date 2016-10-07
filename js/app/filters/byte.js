(function () {
	'use strict';
	/**
	 * @ngdoc filter
	 * @name passmanApp.filter:decrypt
	 * @function
	 * @description
	 * # decrypt
	 * Filter in the passmanApp.
	 */
	angular.module('passmanApp')
		.filter('bytes', function () {
			return function (bytes, precision) {
				if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '-';
				if (typeof precision === 'undefined') precision = 1;
				var units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB'],
					number = Math.floor(Math.log(bytes) / Math.log(1024));
				return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) + ' ' + units[number];
			};
		});
}());