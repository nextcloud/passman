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
				scope.$watch('tooltip', function (newVal, old) {
					if (scope.tooltip) {
						$(el).attr('title', scope.tooltip);
						$(el).tooltip();
						$(el).attr('title',  scope.tooltip).tooltip('fixTitle');
						if($(el).is(':visible')){
							$(el).tooltip('show')
						}

					}
				})
			}
		};
	}]);
