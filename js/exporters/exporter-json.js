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


/** global: PassmanExporter */
PassmanExporter.json = {
	info: {
		name: 'JSON',
		id: 'json',
		description: 'Export credentials as a JSON file.'
	}
};

PassmanExporter.json.export = function (credentials, FileService, EncryptService) {
	/** global: C_Promise */
	return new C_Promise(function () {
		PassmanExporter.getCredentialsWithFiles(credentials, FileService, EncryptService).then((function(){
		    var _output = [];
		    for (var i = 0; i < credentials.length; i++) {
			    var _credential = angular.copy(credentials[i]);
			    
			    delete _credential.vault_key;
			    delete _credential.vault_id;
			    delete _credential.shared_key;
			    
			    _output.push(_credential);

			    var progress = {
				    percent: i / credentials.length * 100,
				    loaded: i,
				    total: credentials.length
			    };
			    this.call_progress(progress);
		    }
		    var file_data = JSON.stringify(_output);
		    this.call_then();
		    download(file_data, 'passman-export.json');
		}).bind(this)).progress(function() {

		});
		
		
	});
};
