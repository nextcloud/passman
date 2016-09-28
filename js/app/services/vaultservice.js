'use strict';

/**
 * @ngdoc service
 * @name passmanApp.VaultService
 * @description
 * # VaultService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('VaultService', ['$http', function ($http) {
		// AngularJS will instantiate a singleton by calling "new" on this function
		var _this = this;
		var _activeVault;
		var service = {
			getVaults: function(){
				var queryUrl = OC.generateUrl('apps/passman/api/v2/vaults');
				return $http.get(queryUrl).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			setActiveVault: function(vault){
				_activeVault = angular.copy(vault);
			},
			getActiveVault: function(){
				return _activeVault;
			},
			getVaultSetting: function(key, default_value){
				if(!_activeVault.vault_settings){
					return default_value
				} else {
					return (_activeVault.vault_settings[key] !== undefined) ? _activeVault.vault_settings[key] : default_value
				}

			},
			setVaultSetting: function(key, value){
				if(!_activeVault.vault_settings){
					return false;
				} else {
					_activeVault.vault_settings[key] = value;
					_this.updateVault(_activeVault);
				}

			},
			createVault: function (vaultName) {
				var queryUrl = OC.generateUrl('apps/passman/api/v2/vaults');
				return $http.post(queryUrl, { vault_name: vaultName }).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			getVault: function (vault) {
				var queryUrl = OC.generateUrl('apps/passman/api/v2/vaults/' + vault.vault_id);
				return $http.get(queryUrl).then(function (response) {
					if(response.data){
						if(response.data.vault_settings){
							response.data.vault_settings = JSON.parse(window.atob(response.data.vault_settings))
						} else {
							response.data.vault_settings = {};
						}
						return response.data;
					} else {
						return response;
					}
				});
			},
			updateVault: function (vault) {
				var _vault = angular.copy(vault);
				delete vault.defaultVaultPass;
				delete vault.defaultVault;
				_vault.vault_settings = window.btoa(JSON.stringify(_vault.vault_settings))
				var queryUrl = OC.generateUrl('apps/passman/api/v2/vaults/' + _vault.vault_id);
				return $http.patch(queryUrl, _vault).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			updateSharingKeys: function (vault) {
				var queryUrl = OC.generateUrl('apps/passman/api/v2/vaults/' + vault.vault_id +'/sharing-keys');
				return $http.post(queryUrl, vault).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			deleteVault: function (vault) {
				var queryUrl = OC.generateUrl('apps/passman/api/v2/vaults/' + vault.vault_id);
				return $http.delete(queryUrl).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			}
		};

		return service;
	}]);
