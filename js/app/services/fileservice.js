'use strict';

/**
 * @ngdoc service
 * @name passmanApp.CredentialService
 * @description
 * # CredentialService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('FileService', ['$http', 'EncryptService', function ($http, EncryptService) {
		return {
			uploadFile: function (file) {
				var queryUrl = OC.generateUrl('apps/passman/api/v2/file');
				var _file = angular.copy(file);
				var data = EncryptService.encryptString(angular.copy(file.data));
				_file.data = data;
				return $http.post(queryUrl, _file).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
			deleteFile: function (file) {
				var queryUrl = OC.generateUrl('apps/passman/api/v2/file/'+ file.file_id);
				var _file = angular.copy(file);
				return $http.delete(queryUrl, _file).then(function (response) {
					if(response.data){
						return response.data;
					} else {
						return response;
					}
				});
			},
		}
	}]);
