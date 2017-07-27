/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @copyright Copyright (c) 2017, Bingen Eguzkitza (bingentxu@gmail.com)
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

// TODO: Allow for new lines and commas in values

// Importers should always start with this
/** global: PassmanImporter */
var PassmanImporter = PassmanImporter || {};
(function(window, $, PassmanImporter) {
	'use strict';
	// Define the importer
	PassmanImporter.padlock = {
		info: {
			name: 'Padlock',
			id: 'padlock',
			exportSteps: ['Create a csv export. Go to Menu -> Settings -> Export Data and copy text into a .csv file']
		}
	};

	PassmanImporter.padlock.readFile = function (file_data) {
		/** global: C_Promise */
		return new C_Promise(function(){
			var rows = PassmanImporter.readCsv(file_data, true);
			var credential_list = [];
			for (var i = 0; i < rows.length; i++) {
				var row = rows[i];
				var _credential = PassmanImporter.newCredential();
				var j = 0;
				for (var k in row) {
					if (!row[k]) {
						continue;
					}
					if (k == 'name') {
						_credential.label = row.name;
						continue;
					}
					if (k == 'username') {
						_credential.username = row.username;
						continue;
					}
					if (k == 'password') {
						_credential.password = row.password;
						continue;
					}
					if (k.toLowerCase() == 'url') {
						_credential.url = row[k];
						continue;
					}
					if (k.toLowerCase() == 'e-mail' ||
					    k.toLowerCase() == 'email') {
						_credential.email = row[k];
						continue;
					}
					if (k.toLowerCase() == 'description') {
						_credential.description = row[k];
						continue;
					}
					_credential.custom_fields[j] = {
						'label' : k,
						'value' : row[k],
						'secret' : true,
						'field_type' : 'text'
					};
					j++;
				}
				if(_credential.label){
					credential_list.push(_credential);
				}

				var progress = {
					percent: i/rows.length*100,
					loaded: i,
					total: rows.length
				};
				this.call_progress(progress);
			}
			this.call_then(credential_list);
		});
	};
})(window, $, PassmanImporter);
