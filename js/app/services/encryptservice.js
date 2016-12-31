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
	 * @name passmanApp.EncryptService
	 * @description
	 * # EncryptService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('EncryptService', ['VaultService', function (VaultService) {
			// AngularJS will instantiate a singleton by calling "new" on this function
			var encryption_config = {
				adata: "",
				iter: 1000,
				ks: 256,
				mode: 'ccm',
				ts: 64
			};

			return {
				encryptString: function (string, _key) {
					if (!_key) {
						_key = VaultService.getActiveVault().vaultKey;
					}
					var rp = {};
					/** global: sjcl */
					var ct = sjcl.encrypt(_key, string, encryption_config, rp);
					return window.btoa(ct);
				},
				decryptString: function (ciphertext, _key) {
					if (!_key) {
						_key = VaultService.getActiveVault().vaultKey;
					}
					ciphertext = window.atob(ciphertext);
					var rp = {};
					try {
						/** global: sjcl */
						return sjcl.decrypt(_key, ciphertext, encryption_config, rp);
					} catch (e) {
						throw e;
					}
				}

			};
		}]);
}());