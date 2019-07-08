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
	PassmanImporter.passmanJson = {
		info: {
			name: 'Passman JSON',
			id: 'passmanJson',
			exportSteps: ['Export the item in passman as passman json, with all fields enabled']
		}
	};

	PassmanImporter.passmanJson.readFile = function (file_data) {
		/** global: C_Promise */
		return new C_Promise(function(){
			var parsed_json = PassmanImporter.readJson(file_data);
			var credential_list = [];
			for (var i = 0; i < parsed_json.length; i++) {
				var item = parsed_json[i];
				var _credential = PassmanImporter.newCredential();
				_credential.label = item.label;
				_credential.username = item.username;
				_credential.password = item.password;
				_credential.email = item.email;
				_credential.url = item.url;
				_credential.tags = item.tags;
				_credential.description = item.description;
				//Check for custom fields
				if (item.hasOwnProperty('customFields')) {
					if (item.customFields.length > 0) {
						for (var cf = 0; cf < item.customFields.length; cf++) {
							_credential.custom_fields.push(
								{
									'label': item.customFields[cf].label,
									'value': item.customFields[cf].value,
									'secret': (item.customFields[cf].clicktoshow === '1')
								}
							)
						}
					}
				}
				// Check for otp
				if (item.hasOwnProperty('otp')) {
					if (item.otp) {
						_credential.otp = {
							'issuer': item.otp.issuer,
							'label': item.otp.label,
							'qr_uri': {
								'image': item.otp.qrCode,
								'qrData': ''
							},
							'secret': item.otp.secret,
							'type': item.otp.type
						}
					}
				}
				if(_credential.label){
					credential_list.push(_credential);
				}
				var progress = {
					percent: i/parsed_json.length*100,
					loaded: i,
					total: parsed_json.length
				};

				this.call_progress(progress);
			}
			this.call_then(credential_list);
		});
	};
})(window, $, PassmanImporter);
