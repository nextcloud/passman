(function () {
	'use strict';
/**
 * @ngdoc directive
 * @name passmanApp.directive:passwordGen
 * @description
 * # passwordGen
 */
angular.module('passmanApp')
	.directive('colorFromString', ['$window', function ($window) {
		return {
			restrict: 'A',
			scope:{
				string: '=colorFromString'
			},
			link: function (scope, el, attr, ctrl) {
				function genColor(str) { // java String#hashCode
					var hash = 0;
					for (var i = 0; i < str.length; i++) {
						hash = str.charCodeAt(i) + ((hash << 5) - hash);
					}
					var c = (hash & 0x00FFFFFF)
						.toString(16)
						.toUpperCase();

					return '#' + '00000'.substring(0, 6 - c.length) + c;
				}
				scope.$watch('string', function(){
					jQuery(el).css('border-color', genColor(scope.string));
				});

			}
		};
	}]);
}());