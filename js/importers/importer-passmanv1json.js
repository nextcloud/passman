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

// Importers should always start with this
/** global: PassmanImporter */
var PassmanImporter = PassmanImporter || {};

(function (window, $, PassmanImporter) {
	'use strict';
	var credential_list = [];
	var _list = [];
	// Define the importer
	PassmanImporter.passmanV1Json = {
		info: {
			name: 'Passman V1 JSON',
			id: 'passmanV1Json',
			exportSteps: ['tbd']
		}
	};

	function uploadFiles (credentialIndex, fileIndex) {
		var vaultKey = angular.element('[ng-controller=MenuCtrl]').scope().active_vault.vaultKey;
		var fileService = angular.element('#app').injector().get('FileService');
	}

	PassmanImporter.passmanV1Json.readFile = function (file_data) {
		/** global: C_Promise */
		return new C_Promise(function () {
			 _list = JSON.parse(file_data);

			var hasFiles = false;
			for (var i = 0; i < _list.length; i++) {
				var import_credential = _list[i];
				var _credential = PassmanImporter.newCredential();
				_credential.label = import_credential.label;
				_credential.username = import_credential.account;
				_credential.email = import_credential.email;
				_credential.password = import_credential.password;
				_credential.tags = import_credential.tags;
				_credential.description = import_credential.description;
				_credential.url = import_credential.url;
				if(import_credential.files.length > 0){
					hasFiles = true;
					_credential.files = import_credential.files;

				} else {
					credential_list.push(_credential);
					var progress = {
						percent: i / _list.length * 100,
						loaded: i,
						total: _list.length
					};

					this.call_progress(progress);
				}
			}
			if(!hasFiles) {
				this.call_then(credential_list);
			} else {
				console.log('Waiting for files to be done!');
			}
		});
	};
})(window, $, PassmanImporter);
