'use strict';

/**
 * @ngdoc service
 * @name passmanApp.EncryptService
 * @description
 * # EncryptService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('EncryptService', ['VaultService', function (VaultService) {
		// AngularJS will instantiate a singleton by calling "new" on this function
		var encryption_config = {
			adata:"",
			iter: 1000,
			ks: 256,
			mode: 'ccm',
			ts:64
		};

		return {
			encryptString: function(string, _key){
				if(!_key) {
					_key = VaultService.getActiveVault().vaultKey;
				}
				var rp = {};
				var ct = sjcl.encrypt(_key, string, encryption_config, rp);
				return  window.btoa(ct);
			},
			decryptString: function(ciphertext, _key){
				if(!_key) {
					_key = VaultService.getActiveVault().vaultKey;
				}
				ciphertext =  window.atob(ciphertext);
				var rp = {};
				try {
					return sjcl.decrypt(_key, ciphertext, encryption_config, rp)
				} catch(e) {
					throw e;
				}
			}

		}
	}]);
