/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function () {
	'use strict';
	/**
	 * @ngdoc service
	 * @name passmanApp.CredentialService
	 * @description
	 * # CredentialService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('CredentialService', ['$http', 'EncryptService', 'VaultService', 'FileService', 'UrlService', function ($http, EncryptService, VaultService, FileService, UrlService) {
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
				'icon': null,
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
				'compromised': false,
				'hidden': false
			};
			var _encryptedFields = ['description', 'username', 'password', 'files', 'custom_fields', 'otp', 'email', 'tags', 'url', 'compromised'];


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

					_credential.expire_time = new Date(angular.copy(credential.expire_time)).getTime() / 1000;

					var queryUrl = UrlService.generateUrl('/api/v2/credentials');
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
				updateCredential: function (credential, skipEncryption, key) {
					let _credential = angular.copy(credential);
					if (!skipEncryption) {
						_credential = this.encryptCredential(credential, key);
					}
					_credential.expire_time = new Date(angular.copy(credential.expire_time)).getTime() / 1000;

					const queryUrl = UrlService.generateUrl('/api/v2/credentials/' + credential.guid);
					return $http.patch(queryUrl, _credential).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				getCredential: function (guid) {
					var queryUrl = UrlService.generateUrl('/api/v2/credentials/' + guid);
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				destroyCredential: function (guid) {
					var queryUrl = UrlService.generateUrl('/api/v2/credentials/' + guid);
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
						if (credential[field] !== undefined) {
							var fieldValue = angular.copy(credential[field]);
							credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue), key);
						}
					}
					return credential;
				},
				decryptCredential: function (credential, key) {
					for (var i = 0; i < _encryptedFields.length; i++) {
						var field = _encryptedFields[i];
						var fieldValue = null;
						if (credential[field] !== undefined) {
							fieldValue = angular.copy(credential[field]);
						}
						var field_decrypted_value;
						try {
							if(fieldValue!==null && fieldValue!=="null" && fieldValue!==""){
								field_decrypted_value = EncryptService.decryptString(fieldValue, key);
							}else{
								field_decrypted_value=null;
							}
						} catch (e) {
							console.error('Error decrypting credential:', credential);
							console.error('Error decrypting credential field:', field);
							throw e;
						}
						try {
							credential[field] = JSON.parse(field_decrypted_value);
						} catch (e) {
							console.warn('Field' + field + ' in ' + credential.label + ' could not be parsed! Value:' + fieldValue);
						}

					}
					return credential;
				},
				getSharedKeyFromCredential: function (credential) {
					var key = null;
					if (!credential.hasOwnProperty('acl') && credential.hasOwnProperty('shared_key')) {
						if (credential.shared_key) {
							key = EncryptService.decryptString(angular.copy(credential.shared_key));
						}
					}
					if (credential.hasOwnProperty('acl')) {
						key = EncryptService.decryptString(angular.copy(credential.acl.shared_key));
					}
					return key;
				},
				getRevisions: function (guid) {
					var queryUrl = UrlService.generateUrl('/api/v2/credentials/' + guid + '/revision');
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				updateRevision: function (revision) {
					var _revision = angular.copy(revision);
					_revision.credential_data = window.btoa(JSON.stringify(_revision.credential_data));
					var queryUrl = UrlService.generateUrl('/api/v2/credentials/' + revision.credential_data.guid + '/revision/' + revision.revision_id);
					return $http.patch(queryUrl, _revision).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				deleteRevision: function (credential_guid, revision_id) {
					var queryUrl = UrlService.generateUrl('/api/v2/credentials/' + credential_guid + '/revision/' + revision_id);
					return $http.delete(queryUrl).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				reencryptCredential: function (credential_guid, old_password, new_password, skipSharingKey) {

					var service = this;

					var progress_datatype = function (current, total, process) {
						this.process = process;
						this.current = current;
						this.total = total;
						this.calculated = current / total * 100;
					};

					var promise_credential_update = function () {
						service.getCredential(credential_guid).then((function (credential) {
							this.parent.plain_credential = service.decryptCredential(credential, this.parent.old_password);
							var plain_credential = angular.copy(this.parent.plain_credential);

							if (
								plain_credential.hasOwnProperty('shared_key') &&
								plain_credential.shared_key !== null &&
								plain_credential.shared_key !== '' &&
								!skipSharingKey
							) {
								// re-encrypt the credential.shared_key with the new password
								// (e.g. re-encrypt from vault_key to generated_shared_key)
								const decrypted_credential_shared_key = EncryptService.decryptString(angular.copy(plain_credential.shared_key)).trim();
								plain_credential.shared_key = EncryptService.encryptString(
									angular.copy(decrypted_credential_shared_key),
									this.parent.new_password
								);
								plain_credential.set_share_key = true;
								plain_credential.skip_revision = true;

								// todo: temporary comment out this Brantje code line as is looks pointless to set the
								// new encryption key to the decrypted_credential_shared_key
								// this.parent.new_password = decrypted_credential_shared_key;
							}

							// before: re-encryption with the now out-commented decrypted_credential_shared_key if the credential has one
							// now: re-encryption with the original parent.new_password (e.g. generated_shared_key)
							this.parent.new_credential_cryptogram = service.encryptCredential(plain_credential, this.parent.new_password);
							this.call_progress(new progress_datatype(1, 2, 'credential'));

							// Save data
							this.parent.new_credential_cryptogram.skip_revision = true;
							service.updateCredential(this.parent.new_credential_cryptogram, true).then((function () {
								this.call_progress(new progress_datatype(2, 2, 'credential'));
								// transfer plain and encrypted credential to the next promise in the complete re-encryption task
								this.call_then({
									plain_text: this.parent.plain_credential,
									cryptogram: this.parent.new_credential_cryptogram
								});
							}).bind(this));
						}).bind(this));
					};

					var promise_files_update = function () {
						if (this.parent.plain_credential.files.length === 0) {
							this.call_progress(new progress_datatype(0, 0, 'files'));
							this.call_then("No files to update");
							return;
						}

						this.total = this.parent.plain_credential.files.length;
						this.current = 0;

						const files_workload = function () {
							const check_next_callback = function () {
								this.current++;
								this.call_progress(new progress_datatype(this.current, this.total, 'files'));

								if (this.current === this.total) {
									this.call_then('All files has been updated');
								} else {
									setTimeout(files_workload.bind(this), 1);
								}
							};

							const _file = this.parent.plain_credential.files[this.current];
							/* jshint ignore:start */
							FileService.getFile(_file).then((function (fileData) {
								try {
									//Decrypt with old key
									fileData.filename = EncryptService.decryptString(fileData.filename, this.parent.old_password);
									fileData.file_data = EncryptService.decryptString(fileData.file_data, this.parent.old_password);

									FileService.updateFile(fileData, this.parent.new_password).then((function () {
										check_next_callback.bind(this)();
									}).bind(this));
								} catch (e) {
									console.error(e);
									console.error('Failed to re-encrypt file. It seems to be corrupt.', _file);
									check_next_callback.bind(this)();
								}
							}).bind(this));
							/* jshint ignore:end */
						};
						setTimeout(files_workload.bind(this), 1);
					};

					var promise_custom_field_files_update = function () {
						if (this.parent.plain_credential.custom_fields.length === 0) {
							this.call_progress(new progress_datatype(0, 0, 'custom_field_files'));
							this.call_then("No custom field files to update");
							return;
						}

						this.total = this.parent.plain_credential.custom_fields.length;
						console.log("total custom_field_files_update = " + this.total);
						this.current = 0;

						const custom_field_workload = function () {
							const check_next_callback = function () {
								this.current++;
								this.call_progress(new progress_datatype(this.current, this.total, 'custom_field_files'));

								if (this.current === this.total) {
									this.call_then('All custom field files has been updated');
								} else {
									setTimeout(custom_field_workload.bind(this), 1);
								}
							};

							if (this.parent.plain_credential.custom_fields[this.current].field_type !== 'file') {
								check_next_callback.bind(this)();
								return;
							}

							const _file = this.parent.plain_credential.custom_fields[this.current].value;
							/* jshint ignore:start */
							FileService.getFile(_file).then((function (fileData) {
								try {
									//Decrypt with old key
									fileData.filename = EncryptService.decryptString(fileData.filename, this.parent.old_password);
									fileData.file_data = EncryptService.decryptString(fileData.file_data, this.parent.old_password);

									FileService.updateFile(fileData, this.parent.new_password).then((function () {
										check_next_callback.bind(this)();
									}).bind(this));
								} catch (e) {
									console.error(e);
									console.error('Failed to re-encrypt custom field file. It seems to be corrupt.', _file);
									check_next_callback.bind(this)();
								}
							}).bind(this));
							/* jshint ignore:end */
						};
						setTimeout(custom_field_workload.bind(this), 1);
					};

					var promise_revisions_update = function () {
						service.getRevisions(this.parent.plain_credential.guid).then((function (revisions) {
							// Double, so we include the actual upload of the data back to the server
							this.total = revisions.length * 2;
							this.upload = 0;
							this.current = 0;
							this.revisions = revisions;

							var revision_workload = function () {
								if (this.revisions.length === 0) {
									this.call_progress(new progress_datatype(0, 0, 'revisions'));
									this.call_then("No history to update");
									return;
								}

								var _revision = revisions[this.current];

								const decrypted_revision_credential_data = service.decryptCredential(_revision.credential_data, this.parent.old_password);
								_revision.credential_data = service.encryptCredential(decrypted_revision_credential_data, this.parent.new_password);

								this.current++;
								this.call_progress(new progress_datatype(this.current + this.upload, this.total, 'revisions'));

								service.updateRevision(_revision).then((function () {
									this.upload++;
									this.call_progress(new progress_datatype(this.upload + this.current, this.total, 'revisions'));
									if (this.current + this.upload === this.total) {
										this.call_then("History updated");
									}
								}).bind(this));

								if (this.current !== (this.total / 2)) {
									setTimeout(revision_workload.bind(this), 1);
								}
							};
							setTimeout(revision_workload.bind(this), 1);
						}).bind(this));
					};

					var promise_workload = function () {
						this.old_password = angular.copy(old_password);
						this.new_password = angular.copy(new_password);
						this.promises = 0;

						var master_promise = this;

						var password_data = function () {
							this.old_password = master_promise.old_password;
							this.new_password = master_promise.new_password;
							this.plain_credential = master_promise.plain_credential;
						};
						this.credential_data = {};
						/** global: C_Promise */
						(new C_Promise(promise_credential_update, new password_data())).progress(function (data) {
							master_promise.call_progress(data);
						}).then(function (data) {
							console.warn("End credential update");
							master_promise.plain_credential = data.plain_text;
							master_promise.promises++;

							master_promise.credential_data = data;
							/** global: C_Promise */
							(new C_Promise(promise_files_update, new password_data())).progress(function (data) {
								master_promise.call_progress(data);
							}).then(function () {
								console.warn("End files update");
								master_promise.promises--;
								if (master_promise.promises === 0) {
									master_promise.call_then(master_promise.credential_data);
								}
							});

							master_promise.promises++;
							/** global: C_Promise */
							(new C_Promise(promise_custom_field_files_update, new password_data())).progress(function (data) {
								master_promise.call_progress(data);
							}).then(function () {
								console.warn("End custom field files update");
								master_promise.promises--;
								if (master_promise.promises === 0) {
									master_promise.call_then(master_promise.credential_data);
								}
							});

							master_promise.promises++;
							/** global: C_Promise */
							(new C_Promise(promise_revisions_update, new password_data())).progress(function (data) {
								master_promise.call_progress(data);
							}).then(function () {
								console.warn("End revisions update");
								master_promise.promises--;
								if (master_promise.promises === 0) {
									master_promise.call_then(master_promise.credential_data);
								}
							});
						});
					};
					/** global: C_Promise */
					return new C_Promise(promise_workload);
				}
			};
		}]);
}());
