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
	 * @name passmanApp.ShareService
	 * @description
	 * # ShareService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('ShareService', ['$http', 'VaultService', 'EncryptService', 'CredentialService', function ($http, VaultService, EncryptService, CredentialService) {
			// Setup sjcl random engine to max paranoia level and start collecting data
			var paranoia_level = 10;
			/** global: sjcl */
			sjcl.random.setDefaultParanoia(paranoia_level);
			sjcl.random.startCollectors();

			return {
				search: function (string) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/search');
					return $http.post(queryUrl, {search: string}).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				shareWithUser: function (credential, target_user_data) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/share');
					return $http.post(queryUrl,
						{
							item_id: credential.credential_id,
							item_guid: credential.guid,
							permissions: target_user_data.accessLevel,
							vaults: target_user_data.vaults,
						}
					);
				},
				getVaultsByUser: function (userId) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/vaults/' + userId);
					return $http.get(queryUrl, {search: userId}).then(function (response) {
						if (response.data) {
							for (var i = 0; i < response.data.length; i++) {
								/** global: forge */
								response.data[i].public_sharing_key = forge.pki.publicKeyFromPem(response.data[i].public_sharing_key);
							}
							return response.data;
						} else {
							return response;
						}
					});
				},
				getPendingRequests: function () {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/pending');
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							return response.data;
						}
					});
				},
				saveSharingRequest: function (request, crypted_shared_key) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/save');
					return $http.post(queryUrl, {
						item_guid: request.item_guid,
						target_vault_guid: request.target_vault_guid,
						final_shared_key: crypted_shared_key
					}).then(function (response) {
						return response.data;
					});
				},
				declineSharingRequest: function (request) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/decline/' + request.req_id);
					return $http.delete(queryUrl).then(function (response) {
						return response.data;
					});
				},
				unshareCredential: function (credential) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/credential/' + credential.guid);
					return $http.delete(queryUrl).then(function (response) {
						return response.data;
					});
				},

				unshareCredentialFromUser: function (credential, user_id) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/credential/' + credential.guid + '/' + user_id);
					return $http.delete(queryUrl).then(function (response) {
						return response.data;
					});
				},

				createPublicSharedCredential: function (shareObj) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/public');
					return $http.post(queryUrl, shareObj).then(function (response) {
						return response.data;
					});
				},
				getPublicSharedCredential: function (credential_guid) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/credential/' + credential_guid + '/public');
					return $http.get(queryUrl).then(function (response) {
							if (response.data) {
								return response;
							} else {
								return response;
							}
						},
						function (result) {
							return result;
						});
				},
				getSharedCredentialACL: function (credential) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/credential/' + credential.guid + '/acl');
					return $http.get(queryUrl).then(function (response) {
							if (response.data) {
								return response.data;
							} else {
								return response;
							}
						},
						function (result) {
							return result;
						});
				},
				updateCredentialAcl: function (credential, acl) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/credential/' + credential.guid + '/acl');
					return $http.patch(queryUrl, acl).then(function (response) {
						return response.data;
					});
				},
				getCredendialsSharedWithUs: function (vault_guid) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/vault/' + vault_guid + '/get');
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							return response.data;
						}
					});
				},
				downloadSharedFile: function (credential, file) {
					var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/credential/' + credential.guid + '/file/' + file.guid);
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							return response.data;
						}
					});
				},
				encryptSharedCredential: function (credential, sharedKey) {
					var _credential = angular.copy(credential);
					_credential.shared_key = EncryptService.encryptString(sharedKey);
					var encrypted_fields = CredentialService.getEncryptedFields();
					for (var i = 0; i < encrypted_fields.length; i++) {
						var field = encrypted_fields[i];
						var fieldValue = angular.copy(credential[field]);
						_credential[field] = EncryptService.encryptString(JSON.stringify(fieldValue), sharedKey);
					}
					return _credential;
				},
				decryptSharedCredential: function (credential, sharedKey) {

					var _credential = angular.copy(credential);
					var encrypted_fields = CredentialService.getEncryptedFields();
					for (var i = 0; i < encrypted_fields.length; i++) {

						var field = encrypted_fields[i];
						var fieldValue = angular.copy(_credential[field]);
						var field_decrypted_value;
						if (_credential.hasOwnProperty(field)) {
							try {
								field_decrypted_value = EncryptService.decryptString(fieldValue, sharedKey);
							} catch (e) {
								if(field === 'compromised' && fieldValue === null){
									//old shares do not have compromised set, so we need to make sure that it will be set for version 2.3.0
									//credentials from 2.3.0 onwards have compromised already set, and don't need to worry about that.
									//if compromised is not the issue, break and throw an error
									field_decrypted_value=0;
								}else{
									throw e;
								}
							}
							try {
								_credential[field] = JSON.parse(field_decrypted_value);
							} catch (e) {
								console.warn('Field' + field + ' in ' + _credential.label + ' could not be parsed! Value:' + fieldValue);
								throw e;
							}
						}
					}
					return _credential;
				},

				generateRSAKeys: function (key_length) {
					/** global: C_Promise */
					var p = new C_Promise(function () {
						/** global: forge */
						var state = forge.pki.rsa.createKeyPairGenerationState(key_length, 0x10001);
						var step = function () {
							// run for 100 ms
							/** global: forge */
							if (!forge.pki.rsa.stepKeyPairGenerationState(state, 100)) {
								if (state.p !== null) {
									// progress(50);
									this.call_progress(50);
								}
								else {
									// progress(0);
									this.call_progress(0);
								}
								setTimeout(step.bind(this), 1);
							}
							else {
								// callback(state.keys);
								this.call_then(state.keys);
							}
						};
						setTimeout(step.bind(this), 100);
					});
					return p;
				},
				generateSharedKey: function (size) {
					size = size || 20;
					/** global: C_Promise */
					return new C_Promise(function () {
						var t = this;
						/** global: CRYPTO */
						CRYPTO.PASSWORD.generate(size,
							function (pass) {
								t.call_then(pass);
							},
							function (progress) {
								t.call_progress(progress);
							}
						);
					});
				},
				rsaKeyPairToPEM: function (keypair) {
					return {
						'publicKey': forge.pki.publicKeyToPem(keypair.publicKey),
						'privateKey': forge.pki.privateKeyToPem(keypair.privateKey)
					};
				},
				getSharingKeys: function () {
					var vault = VaultService.getActiveVault();
					return {
						'private_sharing_key': EncryptService.decryptString(angular.copy(vault.private_sharing_key)),
						'public_sharing_key': vault.public_sharing_key
					};
				},
				rsaPrivateKeyFromPEM: function (private_pem) {
					/** global: forge */
					return forge.pki.privateKeyFromPem(private_pem);
				},
				rsaPublicKeyFromPEM: function (public_pem) {
					/** global: forge */
					return forge.pki.publicKeyFromPem(public_pem);
				},
				/**
				 * Cyphers an array of string in a non-blocking way
				 * @param vaults[]    An array of vaults with the processed public keys
				 * @param string    The string to cypher
				 */
				cypherRSAStringWithPublicKeyBulkAsync: function (vaults, string) {
					var workload = function () {
						if (this.current_index < this.vaults.length > 0 && this.vaults.length > 0) {
							var _vault = angular.copy(this.vaults[this.current_index]);
							/** global: forge */
							_vault.key = forge.util.encode64(
								_vault.public_sharing_key.encrypt(this.string)
							);
							this.data.push(
								_vault
							);
							this.current_index++;

							this.call_progress(this.current_index);
							setTimeout(workload.bind(this), 1);
						}
						else {
							this.call_then(this.data);
						}
					};
					/** global: C_Promise */
					return new C_Promise(function () {
						this.data = [];
						this.vaults = vaults;
						this.string = string;
						this.current_index = 0;

						setTimeout(workload.bind(this), 0);
					});
				}
			};
		}]);
}());
