'use strict';

/**
 * @ngdoc directive
 * @name passmanApp.directive:passwordGen
 * @description
 * # passwordGen
 */
angular.module('passmanApp')
	.directive('progressBar', ['$window', function ($window) {
		return {
			restrict: 'A',
			template:''+
				'<div class="progress">'+
				'<div class="progress-bar" role="progressbar" aria-valuenow="{{progress}}"aria-valuemin="0" aria-valuemax="100" style="width:{{progress}}%;" use-theme>'+
				'<span class="sr-only">{{progress}}% Complete</span>' +
				'<span ng-if="index && total" class="progress-label" use-theme type="\'color\'" color="\'true\'">{{index}} / {{total}}</span>'+
				'</div>'+
				'</div>',
			scope: {
				progress: '=progressBar',
				index: '=index',
				total: '=total'
			},

			link: function (scope, el, attr, ctrl) {

			}
		};
	}]);
