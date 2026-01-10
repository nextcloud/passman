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
	 * @name passmanApp.VaultService
	 * @description
	 * # VaultService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('VaultService', ['UrlService', '$http', function (UrlService, $http) {
			// AngularJS will instantiate a singleton by calling "new" on this function
			var _this = this;
			var _activeVault;
			var service = {
				getVaults: function () {
					var queryUrl = UrlService.generateUrl('/api/v2/vaults');
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				setActiveVault: function (vault) {
					_activeVault = angular.copy(vault);
				},
				getActiveVault: function () {
					return _activeVault;
				},
				getVaultSetting: function (key, default_value) {
					if (!_activeVault.vault_settings) {
						return default_value;
					} else {
						return (_activeVault.vault_settings[key] !== undefined) ? _activeVault.vault_settings[key] : default_value;
					}

				},
				setVaultSetting: function (key, value) {
					if (!_activeVault.vault_settings) {
						return false;
					} else {
						_activeVault.vault_settings[key] = value;
						this.updateVault(_activeVault);
					}

				},
				createVault: function (vaultName) {
					var queryUrl = UrlService.generateUrl('/api/v2/vaults');
					return $http.post(queryUrl, {vault_name: vaultName}).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				getVault: function (vault) {
					var queryUrl = UrlService.generateUrl('/api/v2/vaults/' + vault.guid);
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							if (response.data.vault_settings) {
								response.data.vault_settings = JSON.parse(window.atob(response.data.vault_settings));
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
					delete _vault.defaultVaultPass;
					delete _vault.defaultVault;
					delete _vault.vaultKey;
					_vault.vault_settings = window.btoa(JSON.stringify(_vault.vault_settings));
					var queryUrl = UrlService.generateUrl('/api/v2/vaults/' + _vault.guid);
					return $http.patch(queryUrl, _vault).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				updateSharingKeys: function (vault) {
					var _vault = angular.copy(vault);
					delete _vault.vaultKey;
					var queryUrl = UrlService.generateUrl('/api/v2/vaults/' + _vault.guid + '/sharing-keys');
					return $http.post(queryUrl, _vault).then(function (response) {
						if (response.data) {
							return response.data;
						} else {
							return response;
						}
					});
				},
				reEncryptACLSharingKeys: function (vault, oldVaultPass, newVaultPass, EncryptService) {
					const queryUrl = UrlService.generateUrl('/api/v2/sharing/vault/' + vault.guid + '/acl');
					return $http.get(queryUrl).then(function (response) {
						if (response.data) {
							const updateACLSharingKey = function (index) {
								let acl = response.data[index];
								const decrypted_shared_key = EncryptService.decryptString(angular.copy(acl.shared_key), oldVaultPass);
								acl.shared_key = EncryptService.encryptString(decrypted_shared_key, newVaultPass);

								const patchUrl = UrlService.generateUrl('/api/v2/sharing/credential/' + acl.item_guid + '/acl/shared_key');
								$http.patch(patchUrl, {
									shared_key: acl.shared_key
								}).then(function () {
									if (index < response.data.length - 1) {
										return updateACLSharingKey(index + 1);
									}
								});
							};
							if (response.data[0]) {
								return updateACLSharingKey(0);
							}
						}
					});
				},
				deleteVault: function (vault, file_ids) {
					var queryUrl = UrlService.generateUrl('/api/v2/vaults/' + vault.guid);
					var deleteFilesUrl = UrlService.generateUrl('/api/v2/files/delete');
					var filesData = {
						"file_ids": JSON.stringify(file_ids)
					};
					return $http.post(deleteFilesUrl, filesData).then(function () {
						return $http.delete(queryUrl).then(function (response) {
							if (response.data) {
								return response.data;
							} else {
								return response;
							}
						});
					});
				},
				clearVaultService: function () {
					_activeVault = null;
				}
			};

			return service;
		}]);
}());
