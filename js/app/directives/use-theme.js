'use strict';

/**
 * @ngdoc directive
 * @name passmanApp.directive:passwordGen
 * @description
 * # passwordGen
 */
angular.module('passmanApp')
	.directive('useTheme', ['$window', function ($window) {
		return {
			restrict: 'A',
			link: function (scope, el, attr, ctrl) {
				var _color = $('#header').css('background-color');
				$(el).css('background-color', _color);
			}
		};
	}]);
