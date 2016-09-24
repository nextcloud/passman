// Importers should always start with this
if(!window['PassmanImporter']){
	var PassmanImporter = {}
}

PassmanImporter.parseRow_ = function(row, isHeading) {
	// Strip leading quote.
	row = row.trim();
	if (row.charAt(0) == '"') {
		row = row.substring(1);
	}
	if (row.charAt(row.length - 2) == '"') {
		row = row.substring(0, row.length - 2);
	}
	// Strip trailing quote. There seems to be a character between the last quote
	// and the line ending, hence 2 instead of 1.

    row = row.split('","');
	return row;
};

PassmanImporter.toObject_ = function(headings, row) {
	var result = {};
	for (var i = 0, ii = row.length; i < ii; i++) {
		headings[i] = headings[i].replace(',','_')
			.toLowerCase().replace(' ','_')
			.replace('(','').replace(')','')
			.replace('"','');
		result[headings[i]] = row[i];
	}
	return result;
};

PassmanImporter.join_ = function(arr, sep) {
	var parts = [];
	for (var i = 0, ii = arr.length; i < ii; i++) {
		arr[i] && parts.push(arr[i]);
	}
	return parts.join(sep);
};

PassmanImporter.newCredential = function () {
	var credential = {
		'credential_id': null,
		'guid': null,
		'vault_id': null,
		'label': null,
		'description': null,
		'created': null,
		'changed': null,
		'tags': [],
		'email': null,
		'username': null,
		'password': null,
		'url': null,
		'favicon': null,
		'renew_interval': null,
		'expire_time': 0,
		'delete_time': 0,
		'files': [],
		'custom_fields': [],
		'otp': {},
		'hidden': false
	};
	return credential;
};

PassmanImporter.readCsv = function( csv ){
	var lines = [];
	var rows = csv.split('\n');
	var headings = this.parseRow_(rows[0]);
	for (var i = 1, row; row = rows[i]; i++) {
		row = this.toObject_(headings, this.parseRow_(row));
		lines.push(row);
	}
	return lines;
};