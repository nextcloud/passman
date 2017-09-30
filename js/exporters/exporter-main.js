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
if (!window['PassmanExporter']) {
	var PassmanExporter = {
	    getCredentialsWithFiles: function(credentials, FileService, EncryptService) {
		var t = {
		    cred: credentials,
		    FS: FileService,
		    ES: EncryptService
		};
		/** global: C_Promise */
		return new C_Promise(function() {
		    var _this = this.parent;
		    var credentials = _this.cred;
		    this.parent.total = 0;
		    this.parent.finished = 0;
		    this.parent.fileGUID_cred = [];
		    this.parent.files = [];
		    this.parent.step = (function(file) {
			this.parent.finished ++;
			this.call_progress({
			    total: this.parent.total,
			    finished: this.parent.finished
			});
			
			var dta = this.parent.fileGUID_cred[file.guid];
			
			file.filename = this.parent.ES.decryptString(file.filename, this.parent.cred[dta.cred_pos].vault_key);
			file.file_data = this.parent.ES.decryptString(file.file_data, this.parent.cred[dta.cred_pos].vault_key);
			
			// Files and custom_fields have different field structure
			if (dta.on === 'files') {
			    this.parent.cred[dta.cred_pos][dta.on][dta.at] = file;
			}
			else {
			    this.parent.cred[dta.cred_pos][dta.on][dta.at].value = file;
			}
			
			// We have finished downloading everything, so let's hand over job to somewhere else!
			if (this.parent.total === this.parent.finished) {
			    this.call_then(this.parent.cred);
			}
		    }).bind(this);

		    for (var i = 0; i < credentials.length; i++) {
			
			var item = credentials[i];
			
			// Custom fields
			for (c = 0; c < item.custom_fields.length; c++) {
			    var cf = item.custom_fields[c];
			    if (cf.field_type === 'file') {
				this.parent.total ++;
				this.parent.fileGUID_cred[cf.value.guid] = {
				    cred_pos: i,
				    on: 'custom_fields',
				    at: c
				};

				this.parent.FS.getFile(cf.value).then((function(data){
				    this.parent.step(data);
				}).bind(this));
			    }
			}
			
			// Also get all files
			for (var c = 0; c < item.files.length; c++) {
			    this.parent.total ++;
			    this.parent.fileGUID_cred[item.files[c].guid] = {
				cred_pos: i,
				on: 'files',
				at: c
			    };

			    this.parent.FS.getFile(item.files[c]).then((function(data){
				this.parent.step(data);
			    }).bind(this));
			}
		    }
		    
		    // We have finished downloading everything, so let's hand over job to somewhere else!
		    if (this.parent.total === 0) {
			this.call_then(this.parent.cred);
		    }
		}, t);
	    }
	};
}