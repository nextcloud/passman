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
	.service('NotificationService', ['$timeout', function ($timeout) {
		var to ;
		return {
			showNotification: function (text, time, closeCallback) {
				var notification = OC.Notification.showHtml(text);
				to =$timeout(function () {
					OC.Notification.hide(notification, closeCallback);
				}, time);
				return notification;
			},
			hideNotification: function (notification) {
				$timeout.cancel(to);
				OC.Notification.hide(notification);
			},
			hideAll: function () {
				OC.Notification.hide();
			}
		};
	}]);
}());
