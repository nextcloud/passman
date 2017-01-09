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
		.directive('passwordGen', function ($timeout, $translate) {
			/* jshint ignore:start */
			function Arcfour () {
				this.j = this.i = 0, this.S = []
			}

			function ARC4init (r) {
				var t, n, e;
				for (t = 0; 256 > t; ++t)this.S[t] = t
				for (t = n = 0; 256 > t; ++t)n = n + this.S[t] + r[t % r.length] & 255, e = this.S[t], this.S[t] = this.S[n], this.S[n] = e
				this.j = this.i = 0
			}

			function ARC4next () {
				var r;
				return this.i = this.i + 1 & 255, this.j = this.j + this.S[this.i] & 255, r = this.S[this.i], this.S[this.i] = this.S[this.j], this.S[this.j] = r, this.S[r + this.S[this.i] & 255]
			}

			function prng_newstate () {
				return new Arcfour
			}

			function generatePassword (r, t, n, e, o, i, p, g) {
				var _, a, s, f, d, h, u, l, c, v, w, y, m;
				if (void 0 === r && (r = 8 + get_random(0, 1)), r > 256 && (r = 256, document.getElementById("length").value = 256), i > 256 && (i = 256), void 0 === t && (t = !0), void 0 === n && (n = !0), void 0 === e && (e = !0), void 0 === o && (o = !1), void 0 === i && (i = 0), void 0 === p && (p = !1), void 0 === g && (g = !0), _ = 0, a = 0, s = 0, g && (_ = a = s = 1), f = [], n && _ > 0)for (d = 0; _ > d; d++)f[f.length] = "L"
				if (t && a > 0)for (d = 0; a > d; d++)f[f.length] = "U"
				if (e && i > 0)for (d = 0; i > d; d++)f[f.length] = "D"
				if (o && s > 0)for (d = 0; s > d; d++)f[f.length] = "S"
				for (; f.length < r;)f[f.length] = "A"
				for (f.sort(function () {
					return 2 * get_random(0, 1) - 1
				}), h = "", u = "abcdefghjkmnpqrstuvwxyz", p || (u += "ilo"), n && (h += u), l = "ABCDEFGHJKMNPQRSTUVWXYZ", p || (l += "ILO"), t && (h += l), c = "23456789", p || (c += "10"), e && (h += c), v = "!@#$%^&*", o && (h += v), w = "", y = 0; r > y; y++) {
					switch (f[y]) {
						case"L":
							m = u;
							break;
						case"U":
							m = l;
							break;
						case"D":
							m = c;
							break;
						case"S":
							m = v;
							break;
						case"A":
							m = h
					}
					d = get_random(0, m.length - 1), w += m.charAt(d)
				}
				return w
			}

			function rng_seed_int (r) {
				rng_pool[rng_pptr++] ^= 255 & r, rng_pool[rng_pptr++] ^= r >> 8 & 255, rng_pool[rng_pptr++] ^= r >> 16 & 255, rng_pool[rng_pptr++] ^= r >> 24 & 255, rng_pptr >= rng_psize && (rng_pptr -= rng_psize)
			}

			function rng_seed_time () {
				rng_seed_int((new Date).getTime())
			}

			function rng_get_byte () {
				if (null == rng_state) {
					for (rng_seed_time(), rng_state = prng_newstate(), rng_state.init(rng_pool), rng_pptr = 0; rng_pptr < rng_pool.length; ++rng_pptr)rng_pool[rng_pptr] = 0
					rng_pptr = 0
				}
				return rng_state.next()
			}

			function rng_get_bytes (r) {
				var t;
				for (t = 0; t < r.length; ++t)r[t] = rng_get_byte()
			}

			function SecureRandom () {
			}

			function get_random (r, t) {
				var n, e, o, i = t - r + 1
				for (rng_seed_time(), n = [], e = 0; 4 > e; e++)n[e] = 0
				for (rng_get_bytes(n), o = 0, e = 0; 4 > e; e++)o *= 256, o += n[e]
				return o %= i, o += r
			}

			function get_random_password (r, t) {
				var n;
				var pwlen, newpw;
				for ("number" != typeof r && (r = 12), "number" != typeof t && (t = 16), r > t && (n = r, r = t, t = n), pwlen = get_random(r, t), newpw = ""; newpw.length < pwlen;)newpw += String.fromCharCode(get_random(32, 127))
				return newpw
			}

			var rng_psize, rng_state, rng_pool, rng_pptr, t, z, crypt_obj, num, buf, i
			if (Arcfour.prototype.init = ARC4init, Arcfour.prototype.next = ARC4next, rng_psize = 256, null == rng_pool) {
				/** global: navigator */
				if (rng_pool = [], rng_pptr = 0, "undefined" != typeof navigator && "Netscape" == navigator.appName && navigator.appVersion < "5" && "undefined" != typeof window && window.crypto)for (z = window.crypto.random(32), t = 0; t < z.length; ++t)rng_pool[rng_pptr++] = 255 & z.charCodeAt(t)
				try {
					if (crypt_obj = null, "undefined" != typeof window && void 0 !== window.crypto ? crypt_obj = window.crypto : "undefined" != typeof window && void 0 !== window.msCrypto && (crypt_obj = window.msCrypto), void 0 !== crypt_obj && "function" == typeof crypt_obj.getRandomValues && rng_psize > rng_pptr)for (num = Math.floor((rng_psize - rng_pptr) / 2) + 1, buf = new Uint16Array(num), crypt_obj.getRandomValues(buf), i = 0; i < buf.length; i++)t = buf[i], rng_pool[rng_pptr++] = t >>> 8, rng_pool[rng_pptr++] = 255 & t
				} catch (e) {
				}
				for (; rng_psize > rng_pptr;)t = Math.floor(65536 * sjcl.random.randomWords(1)), rng_pool[rng_pptr++] = t >>> 8, rng_pool[rng_pptr++] = 255 & t
				rng_pptr = 0, rng_seed_time()
			}
			SecureRandom.prototype.nextBytes = rng_get_bytes;
			/* jshint ignore:end */
			return {
				scope: {
					model: "=ngModel",
					length: "@",
					placeholder: "@",
					settings: '=settings',
					callback: '&callback'
				},

				restrict: "E",
				replace: "true",
				template: "" +
				"<div  class=\"            pw-gen            \">" +
				"<div              class=\"input-group                                     \">" +
				"<input  ng-show=\"!passwordVisible\" type=\"password\" ng-disabled=\"disabled\"    class=\"form-control                                    \" ng-model=\"password\" placeholder=\"{{placeholder}}\">" +
				"<input  ng-show=\"passwordVisible\"  type=\"text\"      ng-disabled=\"disabled\"  class=\"form-control                                    \" ng-model=\"password\" placeholder=\"{{placeholder}}\">" +

				'<span class="generate_pw">' +
				'<div class="cell" tooltip="gen_msg" ng-click="generatePasswordStart()"><i class="fa fa-refresh"></i></div>' +
				'<div class="cell" tooltip="tggltxt" ng-click="toggleVisibility()"><i class="fa" ng-class="{\'fa-eye\': passwordVisible, \'fa-eye-slash\': !passwordVisible }"></i></div>' +
				'<div class="cell" tooltip="\'Copy password to clipboard\'"><i class="fa fa-clipboard" ngclipboard-success="onSuccess(e);" ngclipboard-error="onError(e);" ngclipboard data-clipboard-text="{{password}}"></i></div>' +
				"</button>" +
				"</div>" +
				"</div>",
				link: function (scope) {
					scope.callback = scope.callback();
					scope.$watch("model", function () {
						scope.password = scope.model;
					});
					scope.passwordVisible = false;
					scope.toggleVisibility = function () {
						scope.passwordVisible = !scope.passwordVisible;
					};

					scope.passwordNotNull = false;
					scope.$watch("settings", function () {
						if (scope.settings) {
							if (!scope.password && scope.settings.generateOnCreate) {
								scope.generatePasswordStart();
							}
						}
					});

					$translate(['password.gen', 'password.copy', 'copied', 'toggle.visibility']).then(function (translations) {
						scope.tggltxt = translations['toggle.visibility'];
						scope.copy_msg = translations['password.copy'];
						scope.gen_msg = translations['password.gen'];
					});

					scope.$watch("password", function () {
						scope.model = scope.password;
						scope.password_repeat = scope.model;
					});
					//
					scope.onSuccess = function (e) {
						//@TODO move OC.Notification to a service
						OC.Notification.showTemporary($translate.instant('password.copied'));
						e.clearSelection();
					};

					scope.onError = function () {
						OC.Notification.showTemporary('Press Ctrl+C to copy!');
					};
					scope.progressDivShow = false;
					scope.generatePasswordStart = function () {
						scope.progressDivShow = true;
						scope.progressValue = 0;
						scope.progressWidth = {"width": scope.progressValue + "%"};
						scope.generatePasswordProgress();
					};
					scope.generatePasswordProgress = function () {
						$timeout(function () {
							if (scope.progressValue < 100) {
								scope.password = scope._generatePassword(scope.settings);
								scope.progressValue += 10;
								scope.progressWidth = {"width": scope.progressValue + "%"};
								scope.disabled = true;
								scope.generatePasswordProgress();
							} else {
								scope.disabled = false;
								if (scope.callback) {
									scope.callback(scope.password);
								}
							}
						}, 10);
					};


					scope._generatePassword = function (settings) {
						var _settings = {
							'length': 12,
							'useUppercase': true,
							'useLowercase': true,
							'useDigits': true,
							'useSpecialChars': true,
							'minimumDigitCount': 3,
							'avoidAmbiguousCharacters': false,
							'requireEveryCharType': true
						};
						settings = angular.merge(_settings, settings);
						/* jshint ignore:start */
						var password = generatePassword(settings['length'],
							settings.useUppercase,
							settings.useLowercase,
							settings.useDigits,
							settings.useSpecialChars,
							settings.minimumDigitCount,
							settings.avoidAmbiguousCharacters,
							settings.requireEveryCharType);
						/* jshint ignore:end */
						return password;
					};
				}
			};
		});
}());