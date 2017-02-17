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
	PassmanImporter.clippers = {
		info: {
			name: 'Clipperz.is',
			id: 'clippers',
			exportSteps: ['Go to menu -> Export -> Download HTML + JSON. Fields will be imported as custom fields.']
		}
	};

	PassmanImporter.clippers.readFile = function (file_data) {
		/** global: C_Promise */
		return new C_Promise(function() {
			var credential_list = [];
			var re = /<textarea>(.*?)<\/textarea>/gi;
			var matches = re.exec(file_data);
			if(matches){
				var raw_json = matches[0].substring(10);
				raw_json = PassmanImporter.htmlDecode(raw_json.slice(0, -11));
				var json_objects = PassmanImporter.readJson(raw_json);
				for(var i = 0; i < json_objects.length; i++){
					var card = json_objects[i];
					re = /(\w+)/gi;
					var tags = card.label.match(re);
					card.label = card.label.replace(tags.join(' '), '').trim();
					tags = tags.map(function(item){ return {text: item.replace('', '') }});


					var _credential = PassmanImporter.newCredential();
					_credential.label = card.label;
					_credential.description = card.data.notes;
					_credential.tags = tags;
					for(var field in card.currentVersion.fields){
						var field_data = card.currentVersion.fields[field];
						_credential.custom_fields.push(
							{
								'label': field_data.label,
								'value': field_data.value,
								'secret': (field_data.hidden === true)
							}
						)
					}
					if(_credential.label){
						credential_list.push(_credential);
					}
					var progress = {
						percent: i/json_objects.length*100,
						loaded: i,
						total: json_objects.length
					};
					this.call_progress(progress);
				}
			}
			this.call_then(credential_list);
		});
	};
})(window, $, PassmanImporter);
