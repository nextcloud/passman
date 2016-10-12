(function () {
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
				'<div class="value" ng-class="{\'ellipsis\': isLink}">' +
				'<span ng-repeat="n in [] | range:value.length" ng-if="!valueVisible">*</span>' +
				'<span ng-if="valueVisible">{{value}}</span>' +
				'</div>' +
				'<div class="tools">' +
				'<div class="cell" ng-if="toggle" tooltip="\'Toggle visibility\'" ng-click="toggleVisibility()"><i class="fa" ng-class="{\'fa-eye\': !valueVisible, \'fa-eye-slash\': valueVisible }"></i></div>' +
				'<div class="cell" ng-if="isLink"><a ng-href="{{value}}" target="_blank"><i tooltip="\'Open in new window\'" class="link fa fa-external-link"></i></a></div>' +
				'<div class="cell" ngclipboard-success="onSuccess(e);" ngclipboard-error="onError(e);" ngclipboard data-clipboard-text="{{value}}"><i  tooltip="copy_msg" class="fa fa-clipboard"></i></div>' +
				'</div></span>',
				link: function (scope) {
					var expression = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/gi;
					var regex = new RegExp(expression);

					scope.$watch("value", function () {
						if (scope.value) {
							if (scope.secret) {
								scope.valueVisible = false;
							}
							if (scope.value.match(regex)) {
								scope.isLink = true;

							}
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
						}, 5000);
					};
					scope.valueVisible = true;
					scope.toggleVisibility = function () {
						scope.valueVisible = !scope.valueVisible;
					};
				}
			};
		}]);

}());