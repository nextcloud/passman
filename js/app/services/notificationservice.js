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
