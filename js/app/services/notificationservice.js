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
 * @ngdoc service
 * @name passmanApp.NotificationService
 * @description
 * # NotificationService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('NotificationService', function () {
		var DEFAULT_TIMEOUT = 7000;

		function showToast(text, time, closeCallback) {
			if (typeof OCP === 'undefined' || !OCP.Toast) {
				console.error('OCP.Toast is not available');
				return { hideToast: function () {} };
			}

			var options = {
				timeout: time || DEFAULT_TIMEOUT,
			};

			if (closeCallback) {
				options.onRemove = closeCallback;
			}

			return OCP.Toast.message(text, options);
		}

		return {
			showNotification: function (text, time, closeCallback) {
				return showToast(text, time, closeCallback);
			},
			hideNotification: function (notification) {
				if (notification && typeof notification.hideToast === 'function') {
					notification.hideToast();
				}
			},
			hideAll: function () {
				// @nextcloud/dialogs toasts are independent; no global dismiss API
			}
		};
	});
}());
