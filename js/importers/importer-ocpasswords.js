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
		'On the Passwords App, in the bottom left corner, press "Backup and Restore"',
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

	var parseTags = function (row) {
		var tags = [];
		var addTag = function (text) {
			text = (text || '').trim();
			if (!text) {
				return;
			}
			for (var i = 0; i < tags.length; i++) {
				if (tags[i].text === text) {
					return;
				}
			}
			tags.push({text: text});
		};

		if (row.tags) {
			var tagLabels = String(row.tags).split(',');
			for (let tag of tagLabels) {
				addTag(tag);
			}
		}

		return tags;
	};

	var parseEdited = function (edited) {
		if (!edited) {
			return null;
		}
		var timestamp = Date.parse(edited);
		if (!isNaN(timestamp)) {
			return Math.floor(timestamp / 1000);
		}
		return null;
	};

	var parseCustomFields = function (raw, credential) {
		if (!raw) {
			return credential;
		}
		var lines = String(raw).split(/\r?\n/);
		for (var i = 0; i < lines.length; i++) {
			var line = lines[i].trim();
			if (!line) {
				continue;
			}

			var label = '';
			var value = line;
			var type = 'text';
			var colonIndex = line.indexOf(':');
			if (colonIndex !== -1) {
				label = line.substring(0, colonIndex).trim();
				value = line.substring(colonIndex + 1).trim();
				var commaIndex = label.lastIndexOf(',');
				if (commaIndex !== -1) {
					type = label.substring(commaIndex + 1).trim().toLowerCase();
					label = label.substring(0, commaIndex).trim();
				}
			}
			if (!label) {
				label = type;
			}

			// since Passman does not support custom fields of type email, we just import it into our dedicated email field if not already set
			if (type === 'email' && !credential.email) {
				credential.email = value;
				continue;
			}

			// any other custom field type (like "file" of the Password App, that does not contain the actual file) will be imported with type text
			var fieldType = (type === 'secret' || type === 'password') ? 'password' : 'text';
			credential.custom_fields.push({
				label: label,
				value: value,
				secret: fieldType === 'password',
				field_type: fieldType
			});
		}
		return credential;
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
				_credential.tags = parseTags(row);
				parseCustomFields(row.custom_fields, _credential);

				const edited = parseEdited(row.edited);
				if (edited) {
					_credential.changed = edited;
				}

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
