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
		.directive('otpGenerator', ['$compile', '$interval',
			function ($compile, $interval) {
				function mergeDefaultOTPConfig(otp) {
					const defaults = {
						algorithm: "SHA1",
						period: 30,
						digits: 6,
					};

					for (const key in defaults) {
						if (otp[key] === undefined || otp[key] == null) {
							otp[key] = defaults[key];
						}
					}
				}

				return {
					restrict: 'A',
					template: '<span class="otp_generator"><span credential-field value="token" secret="\'true\'"></span> <span ng-bind="timeleft"></span></span>',
					transclude: false,
					scope: {
						otp: '='
					},
					replace: true,
					link: function (scope) {
						scope.token = null;
						scope.timeleft = null;
						scope.timer = null;
						var updateOtp = function () {
							if (!scope.otp || !scope.otp.secret || scope.otp.secret === "") {
								return;
							}
							if (scope.otp.secret.includes(' ')) {
								scope.otp.secret = scope.otp.secret.replaceAll(' ', '');
							}
							mergeDefaultOTPConfig(scope.otp);
							var totp = new OTPAuth.TOTP({
								issuer: scope.otp.issuer,
								label: scope.otp.label,
								algorithm: scope.otp.algorithm,
								digits: scope.otp.digits,
								period: scope.otp.period,
								secret: scope.otp.secret
							});
							scope.token = totp.generate();
						};

						var timer = function () {
							if (scope.otp) {
								var epoch = Math.round(new Date().getTime() / 1000.0);
								scope.timeleft = scope.otp.period - (epoch % scope.otp.period);
								if (epoch % scope.otp.period === 1) updateOtp();
							}
						};
						scope.$watch("otp", function (n) {
							if (n) {
								$interval.cancel(scope.timer);
								updateOtp();
								scope.timer = $interval(timer, 1000);
							}
						}, true);
						scope.$on(
							"$destroy",
							function () {
								$interval.cancel(scope.timer);
							}
						);
					}
				};
			}
		]);
}());
