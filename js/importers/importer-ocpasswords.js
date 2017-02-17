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
	// Define the importer
	var steps = [
		'Backups for the Passwords app need to be enabled on the Admin panel (they are disabled by default).',
		'On the Passwords App, in the bottom left corner, press Settings',
		'Press "Download Backup"',
		'Confirm the export and save the file'
	];
	PassmanImporter.passwordsApp = {
		info: {
			name: 'Passwords App csv',
			id: 'passwordsApp',
			exportSteps: steps
		}
	};

	PassmanImporter.passwordsApp.readFile = function (file_data) {
		/** global: C_Promise */
		var p = new C_Promise(function () {
			var parsed_csv = PassmanImporter.readCsv(file_data);
			var credential_list = [];
			for (var i = 0; i < parsed_csv.length; i++) {
				var row = parsed_csv[i];
				var _credential = PassmanImporter.newCredential();
				_credential.label = row.website + ' - '+ row.username;
				_credential.username = row.username;
				_credential.password = row.password;
				_credential.url = row.fulladdress;
				_credential.description = row.notes;

				credential_list.push(_credential);

				var progress = {
					percent: i / parsed_csv.length * 100,
					loaded: i,
					total: parsed_csv.length
				};

				this.call_progress(progress);
			}
			this.call_then(credential_list);
		});
		return p;
	};
})(window, $, PassmanImporter);
