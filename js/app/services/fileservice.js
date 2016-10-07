(function () {
	'use strict';

	/**
	 * @ngdoc service
	 * @name passmanApp.FileService
	 * @description
	 * # FileService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('FileService', ['$http', 'EncryptService', function ($http, EncryptService) {
			return {
				uploadFile: function (file, key) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/file');
					var _file = angular.copy(file);
					_file.filename = EncryptService.encryptString(_file.filename, key);
					var data = EncryptService.encryptString(angular.copy(file.data), key);
					_file.data = data;
					return $http.post(queryUrl, _file).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				deleteFile: function (file) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/file/' + file.file_id);
					var _file = angular.copy(file);
					return $http.delete(queryUrl, _file).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				getFile: function (file) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/file/' + file.file_id);
					var _file = angular.copy(file);
					return $http.get(queryUrl, _file).then(function (response) {
						if (response.data) {
							if (Object.prototype.toString.call(response.data) === '[object Array]') {
								return response.data.pop();
							} else {
								return response.data;
							}
						} else {
							return response;
						}
					});
				},
				/**
				 * Update a file and it's contents
				 * @param file
				 * @param key Optional encryption key to use
				 * @returns {*}
				 */
				updateFile: function (file, key) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/file/' + file.file_id);
					var _file = angular.copy(file);
					_file.filename = EncryptService.encryptString(_file.filename, key);
					var data = EncryptService.encryptString(angular.copy(file.file_data), key);
					_file.file_data = data;
					return $http.patch(queryUrl, _file).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				dataURItoBlob: function (dataURI, ftype) {
					var byteString, mimeString, ab, ia, bb, i;
					// convert base64 to raw binary data held in a string
					// doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
					byteString = atob(dataURI.split(',')[1]);

					// separate out the mime component
					mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

					// write the bytes of the string to an ArrayBuffer
					ab = new ArrayBuffer(byteString.length);
					ia = new Uint8Array(ab);
					for (i = 0; i < byteString.length; i++) {
						ia[i] = byteString.charCodeAt(i);
					}

					// write the ArrayBuffer to a blob, and you're done
					bb = new Blob([ab], {
						type: ftype
					});

					return URL.createObjectURL(bb);
				}
			};
		}]);
}());