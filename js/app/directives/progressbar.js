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
				'<div class="progress-bar" role="progressbar" aria-valuenow="{{progress}}"aria-valuemin="0" aria-valuemax="100" style="width:{{progress}}%">'+
				'<span class="sr-only">{{progress}}% Complete</span>' +
				'</div>'+
				'</div>',
			scope: {
				progress: '=progressBar'
			},

			link: function (scope, el, attr, ctrl) {

			}
		};
	}]);
