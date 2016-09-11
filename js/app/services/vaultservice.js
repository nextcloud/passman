'use strict';

/**
 * @ngdoc service
 * @name passmanApp.VaultService
 * @description
 * # VaultService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('VaultService', ['$http', 'CacheService', function ($http, CacheService) {
		// AngularJS will instantiate a singleton by calling "new" on this function
		return {
			getVaults: function(){
				var queryUrl = OC.generateUrl('apps/passman/api/v1/vaults');
				return $http.get(queryUrl).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			createVault: function (vaultName) {
				var queryUrl = OC.generateUrl('apps/passman/api/v1/vaults');
				return $http.post(queryUrl, { vault_name: vaultName }).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			getVault: function (vault) {
				var queryUrl = OC.generateUrl('apps/passman/api/v1/vaults/' + vault.vault_id);
				return $http.get(queryUrl).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			updateVault: function (vault) {
				var queryUrl = OC.generateUrl('apps/passman/api/v1/vaults/' + vault.vault_id);
				return $http.post(queryUrl).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			deleteVault: function (vault) {
				var queryUrl = OC.generateUrl('apps/passman/api/v1/vaults/' + vault.vault_id);
				return $http.delete(queryUrl).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			}
		}
	}]);
