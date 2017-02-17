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
	PassmanImporter.EnPassTXT = {
		info: {
			name: 'EnPass text file',
			id: 'EnPassTXT',
			exportSteps: ['Access your Enpass Database. Select "File" > "Export" > "As Text"']
		}
	};

	function parseEnpass(fileData){
		var lastProperty, matches, loginBlocks, property;
		loginBlocks = fileData.replaceAll("Title :","<~passman~>\nTitle :").split('<~passman~>\n').clean("");
		var regex = /(.*) : (.*)/;
		var results = [];
		for(var l = 0; l < loginBlocks.length; l++){
			var loginBlock = loginBlocks[l];
			var lrow = loginBlock.split('\n');
			var result = {};
			for(var r = 0; r < lrow.length; r++){
				var row = lrow[r];
				matches = regex.exec(row);
				if(matches){
					property = matches[1];
					result[property] = matches[2];
				} else {
					if(lastProperty){
						result[lastProperty] += "\n" + row;
					}
				}
				if(property) {
					lastProperty = property;
				}
			}
			results.push(result)
		}
		return results
	}

	PassmanImporter.EnPassTXT.readFile = function (file_data) {
		var mapper = {
			'Title': 'label',
			'Username': 'username',
			'Password': 'password',
			'Email': 'email',
			'Url': 'url',
			'Note': 'description'
		};

		var secret_fields = ['cvc', 'pin', 'security answer'];

		/** global: C_Promise */
		return new C_Promise(function(){
			var credential_list = [];
			var credentials = parseEnpass(file_data);
			for (var i = 0; i < credentials.length; i++) {
				var enpass_credential = credentials[i];
				var new_credential = PassmanImporter.newCredential();
				for(var key in enpass_credential){
					if(!enpass_credential.hasOwnProperty(key)){
						continue;
					}

					if(mapper.hasOwnProperty(key)){
						var prop = mapper[key];
						new_credential[prop] = enpass_credential[key];
					} else {
						if(key !== 'TOTP') {
							var isSecret = (secret_fields.indexOf(key.toLowerCase()) !== -1) ? 1 : 0;
							new_credential.custom_fields.push({
								'label': key,
								'value': enpass_credential[key],
								'secret': isSecret
							})
						}
					}
				}

				if(enpass_credential.hasOwnProperty('TOTP')){
					new_credential.otp.secret = enpass_credential['TOTP'];
				}

				var progress = {
					percent: i/credentials.length*100,
					loaded: i,
					total: credentials.length
				};

				credential_list.push(new_credential);
				this.call_progress(progress);
			}
			this.call_then(credential_list);
		});
	};
})(window, $, PassmanImporter);