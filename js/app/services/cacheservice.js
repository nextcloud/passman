'use strict';

/**
 * @ngdoc service
 * @name passmanApp.CacheService
 * @description
 * # CacheService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('CacheService', ['localStorageService', 'EncryptService', function (localStorageService, EncryptService) {
		return {
			get: function(name){
				return EncryptService.decryptString(localStorageService.get(name));
			},
			set: function (key, value) {
				value = EncryptService.encryptString(value);
				localStorageService.set(key, value);
			}
		}
	}]);
