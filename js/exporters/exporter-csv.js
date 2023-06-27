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
PassmanExporter.csv = {
	info: {
		name: 'CSV',
		id: 'csv',
		description: 'Export credentials as a csv file.'
	}
};

PassmanExporter.csv.export = function (credentials, FileService, EncryptService, _log, $translate) {
	/** global: C_Promise */
	return new C_Promise(function () {
		PassmanExporter.getCredentialsWithFiles(credentials, FileService, EncryptService, _log, $translate).then((function(){
		    const headers = [
				'label',
				'description',
				'created',
				'changed',
				'tags',
				'email',
				'icon',
				'username',
				'password',
				'url',
				'renew_interval',
				'expire_time',
				'delete_time',
				'files',
				'custom_fields',
				'otp',
				'compromised',
				'hidden'
			];
		    let file_data = '"' + headers.join('","') + '"\n';
		    for (let i = 0; i < credentials.length; i++) {
			    const _credential = credentials[i];
			    let row_data = [];
			    for (const field of headers) {
				    if (field === 'tags') {
					    let _tags = [];
					    for (const tag_field of _credential[field]) {
							_tags.push(tag_field.text);
					    }
					    const tag_data = '[' + _tags.join(",") + ']';
					    row_data.push('"' + tag_data.replaceAll('"', '""') + '"');
				    } else if (field === 'custom_fields' || field === 'files' || field === 'otp') {
						let _fields = JSON.stringify(_credential[field]);
						_fields = _fields.replaceAll('"', '""');
						row_data.push('"' + _fields + '"');
					} else {
						let data = _credential[field];
						data = typeof data === 'number' || typeof data === 'boolean' ? "" + data : data;
						const value = (data === null || data === undefined) ? '' : data.replaceAll('"', '""');
						row_data.push('"' + value + '"');
				    }
			    }
			    let progress = {
				    percent: i / credentials.length * 100,
				    loaded: i,
				    total: credentials.length
			    };
			    this.call_progress(progress);
			    file_data += row_data.join(',') + "\n";
		    }
		    this.call_then();
		    download(file_data, 'passman-export.csv', 'text/csv');
		}).bind(this)).progress(function() {
		    
		});
		
		
	});
};
