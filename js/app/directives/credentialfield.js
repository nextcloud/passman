/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function () {
	'use strict';
	/**
	 * @ngdoc directive
	 * @name passmanApp.directive:passwordGen
	 * @description
	 * # passwordGen
	 */

	angular.module('passmanApp')
		.directive('credentialField', ['$timeout', '$translate', function ($timeout, $translate) {
			return {
				scope: {
					value: '=value',
					secret: '=secret',
					inputField: '=useInput',
					inputFieldplaceholder: '=inputPlaceholder',
					isURLFIELD: '=url',
				},
				restrict: 'A',
				replace: 'true',
				template: "" +
				'<span class="credential_field">' +
				'<div class="value" ng-class="{\'ellipsis\': isLink}">' +
				'<span ng-if="secret"><span ng-repeat="n in [] | range:value.length" ng-if="!valueVisible">*</span></span>' +
				'<span ng-if="valueVisible && !inputField" ng-bind-html="value"></span>' +
				'<span ng-if="valueVisible && inputField"><input type="text" ng-model="value" select-on-click placeholder="{{ inputFieldplaceholder }}!"</span>' +
				'</div>' +
				'<div class="tools">' +
				'<div class="cell" ng-if="toggle" tooltip="tggltxt" ng-click="toggleVisibility()"><i class="fa" ng-class="{\'fa-eye\': !valueVisible, \'fa-eye-slash\': valueVisible }"></i></div>' +
				'<div class="cell" ng-if="isURLFIELD && isLink"><a ng-href="{{value}}" target="_blank" rel="nofollow noopener noreferrer"><i tooltip="\'Open in new window\'" class="link fa fa-external-link"></i></a></div>' +
				'<div class="cell" ng-if="isURLFIELD && isPartialLink"><a ng-href="//{{value}}" target="_blank" rel="nofollow noopener noreferrer"><i tooltip="\'Open in new window\'" class="link fa fa-external-link"></i></a></div>' +
				'<div class="cell" ngclipboard-success="onSuccess(e);" ngclipboard-error="onError(e);" ngclipboard data-clipboard-text="{{value}}"><i tooltip="copy_msg" class="fa fa-files-o"></i></div>' +
				'</div></span>',
				link: function (scope) {
					var expression = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/i;
					var regex = new RegExp(expression);
					$translate(['toggle.visibility','copy.field', 'copy', 'copied']).then(function (translations) {
						scope.tggltxt = translations['toggle.visibility'];
						scope.copy_msg = translations['copy.field'];
					});

					scope.$watch("value", function () {
						if (scope.value) {
							if (scope.secret) {
								scope.valueVisible = false;
							}
							if (regex.test(scope.value)) {
								scope.isLink = true;
								scope.isPartialLink = false;
							} else {
								scope.isLink = false;
								if(regex.test('https://'+scope.value)){
									scope.isPartialLink = true;
								}
							}

						}
					});
					if (!scope.toggle) {
						if (scope.secret) {
							scope.toggle = true;
						}
					}

					var timer;
					scope.onSuccess = function () {
						scope.copy_msg = $translate.instant('copied') ;
						$timeout.cancel(timer);
						timer = $timeout(function () {
							scope.copy_msg = $translate.instant('copy');
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
