'use strict';

/**
 * @ngdoc service
 * @name passmanApp.VaultService
 * @description
 * # VaultService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('SettingsService', ['localStorageService', function (localStorageService) {
		var settings = {
			defaultVault: null,
			defaultVaultPassword: null
		};

		var cookie = localStorageService.get('settings');
		settings = angular.merge(settings, cookie);
		return {
			getSettings: function(){
				return settings
			},
			getSetting: function(name){
				return settings[name]
			},
			setSetting: function (name, value) {
				settings[name] = value;
				localStorageService.set('settings', settings);
			}
		}
	}]);
