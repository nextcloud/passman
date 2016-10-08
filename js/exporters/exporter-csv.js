// Importers should always start with this
if (!window['PassmanExporter']) {
	var PassmanExporter = {}
}
// Define the importer
/** global: PassmanExporter */
PassmanExporter.csv = {
	info: {
		name: 'CSV',
		id: 'csv',
		description: 'Export credentials as csv.'
	}
};

PassmanExporter.csv.export = function (credentials) {
	/** global: C_Promise */
	return new C_Promise(function () {
		var _this = this;
		var headers = ['label','username','password','email','description','tags'];
		var file_data = '"'+headers.join('","')+'"\n';
		for(var i = 0; i < credentials.length; i++){
			var _credential = credentials[i];
			var row_data = [];
			for(var h=0; h < headers.length; h++ ){
				var field = headers[h];
				if(field === 'tags'){
					var _tags = [];
					for(var t = 0; t < _credential[field].length; t++){
						_tags.push(_credential[field][t].text);
					}
					var data = '[' + _tags.join(",") + ']';
					row_data.push('"' + data + '"');
				} else {
					row_data.push('"' + _credential[field] + '"');
				}
			}
			var progress = {
				percent: i/credentials.length*100,
				loaded: i,
				total: credentials.length
			};
			_this.call_progress(progress);
			file_data += row_data.join(',')+"\n";
		}
		_this.call_then();
		download(file_data, 'passman-export.csv');
	});
};
