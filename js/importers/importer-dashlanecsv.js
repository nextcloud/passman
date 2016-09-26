// Importers should always start with this
if (!window['PassmanImporter']) {
	var PassmanImporter = {}
}
// Define the importer
PassmanImporter.dashLaneCsv = {
	info: {
		name: 'Dashlane 4 csv',
		id: 'dashLaneCsv',
		description: 'Create an csv export. Go to File -> export -> Unsecured archive (readable) in CSV format'
	}
};

PassmanImporter.dashLaneCsv.readFile = function (file_data, callback) {
	return new C_Promise(function(){
		var rows = file_data.split('\n');
		var credential_list = [];
		for (var i = 1, row; row = rows[i]; i++) {
			row = rows[i];
			var row_data = row.split('","');
			if (row_data[0].charAt(0) == '"') {
				row_data[0] = row_data[0].substring(1);
			}

			if (row_data[row_data.length-1].toString().charAt(row_data[row_data.length - 1].length - 1) == '"') {
				row_data[row_data.length - 1] = row_data[row_data.length -1].substring(0, row_data[row_data.length - 1].length - 1);
			}

			var _credential = PassmanImporter.newCredential();
			_credential.label = row_data[0];
			_credential.username = row_data[2];
			_credential.password = row_data[row_data.length - 2];
			_credential.url = row_data[0];
			_credential.description = row_data[row_data.length - 1];
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