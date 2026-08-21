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
		'On the Passwords App, in the bottom left corner, press Settings',
		'Open the "Backup or export" section',
		'Select "Predefined CSV" as export format and check "Export Passwords"',
		'Press "Export" and save the downloaded CSV file'
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
				var username = row.username || '';
				var label = row.label || PassmanImporter.join_([row.website, username], ' - ');
				var _credential = PassmanImporter.newCredential();
				_credential.label = label;
				_credential.username = username;
				_credential.password = row.password || '';
				_credential.url = row.url || row.fulladdress || '';
				_credential.description = row.notes || '';

				if (label) {
					credential_list.push(_credential);
				}

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
