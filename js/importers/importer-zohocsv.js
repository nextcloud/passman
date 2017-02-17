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
	PassmanImporter.zohoCsv = {
		info: {
			name: 'ZOHO csv',
			id: 'zohoCsv',
			exportSteps: ['Create an csv export. Go to Tools ->  Export secrets -> Select "General CSV" and click "Export Secrets"']
		}
	};

	PassmanImporter.zohoCsv.readFile = function (file_data) {
		/** global: C_Promise */
		return new C_Promise(function () {
			var parsed_csv = PassmanImporter.readCsv(file_data);

			var credential_list = [];
			for (var i = 0; i < parsed_csv.length; i++) {
				var row = parsed_csv[i];
				var _credential = PassmanImporter.newCredential();
				_credential.label = row.secret_name;
				_credential.url = row.secret_url;
				_credential.description = row.notes;
				if (row.hasOwnProperty('secretdata')) {
					var rows = row.secretdata.split("\n");
					for (var r = 0; r < rows.length; r++) {
						var cells = rows[r].split(':');
						var key = cells[0];
						var value = cells.slice(1).join(':');
						if (key && value) {
							_credential.custom_fields.push(
								{
									'label': key,
									'value': value,
									'secret': false
								}
							);
						}
					}
				}
				if (_credential.label !== "") {
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
	};
})(window, $, PassmanImporter);
