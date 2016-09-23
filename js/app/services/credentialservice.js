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
			'tags': [],
			'email': null,
			'username': null,
			'password': null,
			'url': null,
			'favicon': null,
			'renew_interval': null,
			'expire_time': 0,
			'delete_time': 0,
			'files': [],
			'custom_fields': [],
			'otp': {},
			'hidden': false
		};
		var _encryptedFields = ['description', 'username', 'password', 'files', 'custom_fields', 'otp', 'email', 'tags', 'url'];


		return {
			newCredential: function () {
				return angular.copy(credential);
			},
			createCredential: function (credential) {
				var _credential = angular.copy(credential);
				for (var i = 0; i < _encryptedFields.length; i++) {
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					_credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue));
				}

				_credential.expire_time = new Date( angular.copy(credential.expire_time) ).getTime() / 1000;

				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials');
				return $http.post(queryUrl, _credential).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			updateCredential: function (credential) {
				var _credential = angular.copy(credential);
				for (var i = 0; i < _encryptedFields.length; i++) {
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					_credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue));
				}
				_credential.expire_time = new Date( angular.copy(credential.expire_time) ).getTime() / 1000;

				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials/' + credential.credential_id);
				return $http.patch(queryUrl, _credential).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			getCredential: function(id){
				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials/' + id);
				return $http.get(queryUrl).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			destroyCredential: function(id){
				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials/' + id);
				return $http.delete(queryUrl).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			encryptCredential: function (credential) {
				for (var i = 0; i < _encryptedFields.length; i++) {
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue));
				}
				return credential;
			},
			decryptCredential: function (credential) {
				for (var i = 0; i < _encryptedFields.length; i++) {
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					try{
						credential[field] = JSON.parse(EncryptService.decryptString(fieldValue));
					} catch (e){
						console.log('Field' + field + ' in '+ credential.label +' could not be parsed! Value:'+ fieldValue)
					}

				}
				return credential;
			}
		}
	}]);
