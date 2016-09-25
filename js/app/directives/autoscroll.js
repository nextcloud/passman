'use strict';

/**
 * @ngdoc directive
 * @name passmanApp.directive:autoScroll
 * @description
 * # autoScroll
 */
angular.module('passmanApp')
	.directive('autoScroll', function () {
		return {
			restrict: 'A',
			scope: {
				autoScroll: '='
			},
			link: function postLink(scope, element, attrs) {
				scope.$watch('autoScroll', function () {
					$('#import_log').scrollTop($('#import_log')[0].scrollHeight);
				}, true);
			}
		};
	});
