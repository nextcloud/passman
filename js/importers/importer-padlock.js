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
			var rows = file_data.split('\n');
			var credential_list = [];
			var titles = rows[0].split(',');
			for (var i = 1; i < rows.length; i++) {
				var row = rows[i];
				var row_data = row.split(',');
				if (row_data[0].charAt(0) === '"') {
					row_data[0] = row_data[0].substring(1);
				}

				if (row_data[row_data.length-1].toString().charAt(row_data[row_data.length - 1].length - 1) === '"') {
					row_data[row_data.length - 1] = row_data[row_data.length -1].substring(0, row_data[row_data.length - 1].length - 1);
				}

				var _credential = PassmanImporter.newCredential();
				_credential.label = row_data[0];
				_credential.username = row_data[2];
				_credential.password = row_data[3];
				var k = 0;
				for (var j = 4; j < titles.length; j++) {
					if (!row_data[j])
						continue;
					if (titles[j].toLowerCase() == 'url') {
						_credential.url = row_data[j];
						continue;
					}
					if (titles[j].toLowerCase() == 'e-mail'
					    || titles[j].toLowerCase() == 'email') {
						_credential.email = row_data[j];
						continue;
					}
					if (titles[j].toLowerCase() == 'description') {
						_credential.description = row_data[j];
						continue;
					}
					_credential.custom_fields[k] = {
						'label' : titles[j],
						'value' : row_data[j],
						'secret' : true,
						'field_type' : 'text'
					};
					k++;
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
