/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2026, Timo Triebensky (timo@binsky.org)
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

/* global TextEncoder, TextDecoder, Uint8Array */
(function () {
	'use strict';

	/**
	 * @ngdoc service
	 * @name passmanApp.Utf8Base64Service
	 * @description
	 * UTF-8 base64 for text that may contain characters outside Latin-1.
	 * window.btoa throws on those characters. decode runs window.atob and then reads the bytes as UTF-8,
	 * which is the same recovery as PHP base64_decode + json_decode. ASCII text matches the old btoa/atob pair.
	 * Since the old frontend stack does not provide js-base64, let's simply use the existing TextEncoder and TextDecoder APIs.
	 */
	angular.module('passmanApp')
		.service('Utf8Base64Service', [function () {
			return {
				encode: function (value) {
					var bytes = new TextEncoder().encode(value);
					var binary = '';
					var i;
					for (i = 0; i < bytes.length; i++) {
						binary += String.fromCharCode(bytes[i]);
					}
					return window.btoa(binary);
				},
				decode: function (value) {
					var binary = window.atob(value);
					var bytes = new Uint8Array(binary.length);
					var i;
					for (i = 0; i < binary.length; i++) {
						bytes[i] = binary.charCodeAt(i);
					}
					return new TextDecoder().decode(bytes);
				}
			};
		}]);
}());
