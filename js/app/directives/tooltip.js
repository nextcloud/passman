(function () {
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
							jQuery(el).attr('title', scope.tooltip);
							jQuery(el).tooltip();
							jQuery(el).attr('title', scope.tooltip).tooltip('fixTitle');
							jQuery('.tooltip-inner').text(scope.tooltip); // Dirty hack
							if (jQuery(el).is(':visible')) {
								//$(el).tooltip('show')
							}

						}
					});
				}
			};
		}]);
}());