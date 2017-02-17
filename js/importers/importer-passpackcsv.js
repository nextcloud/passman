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
(function(window, $, PassmanImporter) {
	'use strict';
	// Define the importer
	PassmanImporter.passpackCsv = {
		info: {
			name: 'Passpack csv',
			id: 'passpackCsv',
			exportSteps: ['Go to Tools -> Export. Select Comma Separated Values, All entries then continue.']
		}
	};

	PassmanImporter.passpackCsv.readFile = function (file_data) {
		/** global: C_Promise */
		return new C_Promise(function(){
			var parsed_csv = PassmanImporter.readCsv(file_data, false);
			var credential_list = [];
			for (var i = 0; i < parsed_csv.length; i++) {
				var row = parsed_csv[i];
				var _credential = PassmanImporter.newCredential();
				_credential.label = row[0];
				_credential.username = row[1];
				_credential.password = row[2];
				_credential.url = row[3];
				var tags = row[4].split(' ');
				if (tags.length > 0) {
					_credential.tags = tags.map(function (item) {
						if (item) {
							return {text: item}
						}

					}).filter(function (item) {
						return (item);
					});
				}
				_credential.description = row[5];
				_credential.email = row[6];
				if (_credential.label) {
					credential_list.push(_credential);
				}

				var progress = {
					percent: i/parsed_csv.length*100,
					loaded: i,
					total: parsed_csv.length
				};

				this.call_progress(progress);
			}
			this.call_then(credential_list);
		})
	};
})(window, $, PassmanImporter);