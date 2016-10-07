(function () {
	'use strict';


	/**
	 * @ngdoc service
	 * @name passmanApp.SettingsService
	 * @description
	 * # SettingsService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('SettingsService', ['localStorageService', function (localStorageService) {
			var settings = {
				defaultVault: null,
				defaultVaultPass: null
			};
			var neverSend = ['defaultVault', 'defaultVaultPass'];

			var cookie = localStorageService.get('settings');
			settings = angular.merge(settings, cookie);
			return {
				getSettings: function () {
					return settings;
				},
				getSetting: function (name) {
					return settings[name];
				},
				setSetting: function (name, value) {
					settings[name] = value;
					localStorageService.set('settings', settings);
				}
			};
		}]);
}());