'use strict';

/**
 * @ngdoc service
 * @name passmanApp.CredentialService
 * @description
 * # CredentialService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('CredentialService', ['$http', 'EncryptService', function ($http, EncryptService) {
		var credential = {
			'credential_id': null,
			'guid': null,
			'vault_id': null,
			'label': null,
			'description': null,
			'created': null,
			'changed': null,
			'tags': null,
			'email': null,
			'username': null,
			'password': null,
			'url': null,
			'favicon': null,
			'renew_interval': null,
			'expire_time': null,
			'delete_time': null,
			'files': null,
			'custom_fields': null,
			'otp': null,
			'hidden': false
		};
		var _encryptedFields = ['description','username','password','files','custom_fields','otp'];
		return {
			newCredential: function () {
				return angular.extend({}, credential);
			},
			createCredential: function (credential) {
				for(var i = 0; i < _encryptedFields.length; i++){
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue));
				}

				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials');
				return $http.post(queryUrl, credential).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			updateCredential: function (credential) {
				for(var i = 0; i < _encryptedFields.length; i++){
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue));
				}

				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials/'+ credential.credential_id);
				return $http.post(queryUrl, credential).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			decryptCredential: function (credential) {
				for(var i = 0; i < _encryptedFields.length; i++){
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					credential[field] = JSON.parse(EncryptService.decryptString(fieldValue));
				}
				return credential;
			}
		}
	}]);
