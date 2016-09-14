'use strict';

/**
 * @ngdoc directive
 * @name passmanApp.directive:passwordGen
 * @description
 * # passwordGen
 */
angular.module('passmanApp')
	.directive('tooltip', ['$window', function ($window) {
		return {
			restrict: 'A',
			scope: {
				tooltip: '=tooltip'
			},

			link: function (scope, el, attr, ctrl) {
				scope.$watch('tooltip', function(){
					$(el).attr('title', scope.tooltip);
					$(el).tooltip()
				})
			}
		};
	}]);
