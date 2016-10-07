(function () {
	'use strict';

	/**
	 * @ngdoc directive
	 * @name passmanApp.directive:passwordGen
	 * @description
	 * # passwordGen
	 */
	angular.module('passmanApp')
		.directive('otpGenerator', ['$compile', '$timeout',
			function ($compile, $timeout) {
				function dec2hex (s) {
					return (s < 15.5 ? '0' : '') + Math.round(s).toString(16);
				}

				function hex2dec (s) {
					return parseInt(s, 16);
				}

				function base32tohex (base32) {
					if (!base32) {
						return;
					}
					var base32chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
					var bits = "";
					var hex = "";
					var i;
					for (i = 0; i < base32.length; i++) {
						var val = base32chars.indexOf(base32.charAt(i).toUpperCase());
						bits += leftpad(val.toString(2), 5, '0');
					}

					for (i = 0; i + 4 <= bits.length; i += 4) {
						var chunk = bits.substr(i, 4);
						hex = hex + parseInt(chunk, 2).toString(16);
					}
					return hex;

				}

				function leftpad (str, len, pad) {
					if (len + 1 >= str.length) {
						str = Array(len + 1 - str.length).join(pad) + str;
					}
					return str;
				}

				return {
					restrict: 'A',
					template: '<span class="otp_generator"><span credential-field value="otp" secret="\'true\'"></span> <span ng-bind="timeleft"></span></span>',
					transclude: false,
					scope: {
						secret: '='
					},
					replace: true,
					link: function (scope, element) {
						scope.otp = null;
						scope.timeleft = null;
						scope.timer = null;
						var updateOtp = function () {
							if (!scope.secret) {
								return;
							}
							var key = base32tohex(scope.secret);
							var epoch = Math.round(new Date().getTime() / 1000.0);
							var time = leftpad(dec2hex(Math.floor(epoch / 30)), 16, '0');
							var hmacObj = new jsSHA(time, 'HEX');
							var hmac = hmacObj.getHMAC(key, 'HEX', 'SHA-1', "HEX");
							var offset = hex2dec(hmac.substring(hmac.length - 1));
							var otp = (hex2dec(hmac.substr(offset * 2, 8)) & hex2dec('7fffffff')) + '';
							otp = (otp).substr(otp.length - 6, 6);
							scope.otp = otp;

						};

						var timer = function () {
							var epoch = Math.round(new Date().getTime() / 1000.0);
							var countDown = 30 - (epoch % 30);
							if (epoch % 30 === 0) updateOtp();
							scope.timeleft = countDown;
							scope.timer = $timeout(timer, 1000);

						};
						scope.$watch("secret", function (n) {
							if (n) {
								$timeout.cancel(scope.timer);
								updateOtp();
								timer();
							} else {
								$timeout.cancel(scope.timer);
							}
						}, true);
						scope.$on(
							"$destroy",
							function (event) {
								$timeout.cancel(scope.timer);
							}
						);
					}
				};
			}
		]);
}());