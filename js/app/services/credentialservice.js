'use strict';

/**
 * @ngdoc service
 * @name passmanApp.CredentialService
 * @description
 * # CredentialService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('CredentialService', ['$http', 'EncryptService', 'VaultService', function ($http, EncryptService, VaultService) {
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
			getEncryptedFields: function () {
				return _encryptedFields;
			},
			updateCredential: function (credential, skipEncyption) {
				var _credential = angular.copy(credential);
				if(!skipEncyption){
					for (var i = 0; i < _encryptedFields.length; i++) {
						var field = _encryptedFields[i];
						var fieldValue = angular.copy(credential[field]);
						_credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue));
					}
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
			encryptCredential: function (credential, key) {
				for (var i = 0; i < _encryptedFields.length; i++) {
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);
					credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue), key);
				}
				return credential;
			},
			decryptCredential: function (credential, key) {
				for (var i = 0; i < _encryptedFields.length; i++) {
					var field = _encryptedFields[i];
					var fieldValue = angular.copy(credential[field]);

					try {
						var field_decrypted_value = EncryptService.decryptString(fieldValue, key)
					} catch (e){
						console.log(e)
						throw e
					}
					try{
						credential[field] = JSON.parse(field_decrypted_value);
					} catch (e){
						console.log('Field' + field + ' in '+ credential.label +' could not be parsed! Value:'+ fieldValue)

					}

				}
				return credential;
			},
			getRevisions:  function(id){
				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials/' + id + '/revision');
				return $http.get(queryUrl).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			updateRevision:  function(revision){
				var _revision = angular.copy(revision);
				_revision.credential_data = window.btoa(JSON.stringify(_revision.credential_data));
				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials/' + revision.credential_data.credential_id + '/revision/' + revision.revision_id);
				return $http.patch(queryUrl, _revision).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			deleteRevision:  function(credential_id, revision_id){
				var queryUrl = OC.generateUrl('apps/passman/api/v2/credentials/' + credential_id + '/revision/' + revision_id);
				return $http.delete(queryUrl).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			reencryptCredential: function(credential_id, old_password, new_password){
				var progress_datatype = function(current, total){
					this.current = current;
					this.total = total;
					this.calculated = current / total * 100;
				};

				var promise_credential_update = (function(){
					this.getCredential(credential_id).then((function (credential) {
						this.plain_credential = this.decryptCredential(credential, this.old_password);
						this.new_credential_cryptogram = this.encryptCredential(this.temp_data.plain_credential, this.new_password);

						this.call_progress(new progress_datatype(1, 2));

						// Save data
						this.updateCredential(this.temp_data.new_credential_cryptogram, true).then((function(){
							this.call_progress(new progress_datatype(2, 2));
							this.call_then(this.plain_credential);
						}).bind(this));
					}).bind(this));
				}).bind(this);

				var promise_files_update = (function(){
					// Add the double of the files so we take encryption phase and upload to the server into the math
					this.total = this.plain_credential.files.length * 2;	 // Binded on credential finish upload
					this.current = 0;

					for (var i = 0; i < this.plain_credential.files.length; i++){
						var _file = this.plain_credential.files[i];
						FileService.getFile(_file).then((function (fileData) {
							//Decrypt with old key
							fileData.filename = EncryptService.decryptString(fileData.filename, this.old_password);
							fileData.file_data = EncryptService.decryptString(fileData.file_data, this.old_password);

							this.current ++;

							this.call_progress(new progress_datatype(this.current, this.total));

							FileService.updateFile(fileData, this.new_password).then((function(data){
								this.current++;
								this.call_progress(new progress_datatype(this.current, this.total));
								if (this.current == this.total) {
									this.call_then('All files has been updated');
								}
							}).bind(this));
						}).bind(this));
					}
				}).bind(this);

				var promise_revisions_update = (function(){
					CredentialService.getRevisions(this.plain_credential.guid).then((function (revisions) {
						// Double, so we include the actual upload of the data back to the server
						this.total = revisions.length * 2;
						this.upload = 0;
						this.current = 0;
						this.revisions = revisions;

						var revision_workload = function(){
							var _revision = revisions[this.current];
							//Decrypt!
							_revision.credential_data = this.decryptCredential(_revision.credential_data, this.old_password);
							_revision.credential_data = ShareService.encryptSharedCredential(_revision.credential_data, this.new_password);
							console.log('Used key for encrypting history ', this.new_password);
							this.current ++;

							this.call_progress(new progress_datatype(this.current + this.upload, this.total));

							this.updateRevision(_revision).then((function(data){
								this.upload ++;
								this.call_progress(new progress_datatype(this.upload + this.current, this.total));
								if (this.current + this.upload == this.total){
									this.call_then("History updated");
								}
							}).bind(this));

							setTimeout(revision_workload.bind(this), 1);
						};
					}).bind(this));
				}).bind(this);

				var promise_workload = (function(){
					this.old_password = angular.copy(old_password);
					this.new_password = angular.copy(new_password);
					this.promises = 0;

					(new C_Promise(promise_credential_update.bind(this))).progress(function(data){
						this.call_progress(data);
					}).then(function(data){
						this.plain_credential = data;
						this.promises ++;
						(new C_Promise(promise_files_update.bind(this))).progress(function(data){
							this.call_progress(data);
						}).then(function(data){
							this.promises --;
							if (this.promises == 0){
								this.call_then("All done");
							}
						});
						this.promises ++;
						(new C_Promise(promise_revisions_update.bind(this))).progress(function(data){
							this.call_progress(data);
						}).then(function(data){
							this.promises --;
							if (this.prmises == 0){
								this.call_then("All done");
							}
						})
					});
				}).bind(this);

				return new C_Promise(promise_workload);
			}
		}
	}]);
