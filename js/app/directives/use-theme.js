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
			scope:{
				type: '=type'
			},
			link: function (scope, el, attr, ctrl) {
				var _color = $('#header').css('background-color');
				if(!scope.type) {
					$(el).css('background-color', _color);
				} else {
					$(el).css(scope.type, _color);
				}
			}
		};
	}]);
