'use strict';

/**
 * @ngdoc directive
 * @name passmanApp.directive:passwordGen
 * @description
 * # passwordGen
 */

angular.module('passmanApp')
	.directive('credentialField', ['$timeout', function ($timeout) {
		return {
			scope: {
				value: '=value',
				secret: '=secret'
			},
			restrict: 'A',
			replace: 'true',
			template: "" +
			'<span class="credential_field">' +
			'<div class="value">' +
			'<span ng-repeat="n in [] | range:value.length" ng-if="!valueVisible">*</span>' +
			'<span ng-if="valueVisible">{{value}}</span>' +
			'</div>' +
			'<div class="tools">' +
			'<div class="cell" ng-if="toggle" tooltip="\'Toggle visibility\'" ng-click="toggleVisibility()"><i class="fa" ng-class="{\'fa-eye\': !valueVisible, \'fa-eye-slash\': valueVisible }"></i></div>' +
			'<div class="cell" ngclipboard-success="onSuccess(e);" ngclipboard-error="onError(e);" ngclipboard data-clipboard-text="{{value}}"><i  tooltip="copy_msg" class="fa fa-clipboard"></i></div>' +
			'</div></span>',
			link: function (scope, elem, attrs, modelCtrl) {
				scope.$watch("value", function () {
					if (scope.secret) {
						scope.valueVisible = false;
					}
				});
				if (!scope.toggle) {
					if (scope.secret) {
						scope.toggle = true;
					}
				}
				scope.copy_msg = 'Copy to clipboard';
				var timer;
				scope.onSuccess = function () {
					scope.copy_msg = 'Copied to clipboard!';
					$timeout.cancel(timer);
					timer = $timeout(function () {
						scope.copy_msg = 'Copy to clipboard';
					}, 5000)
				}
				scope.valueVisible = true;
				scope.toggleVisibility = function () {
					scope.valueVisible = !scope.valueVisible;
				};
			}
		};
	}]);
