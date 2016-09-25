// Importers should always start with this
if (!window['PassmanImporter']) {
	var PassmanImporter = {}
}
// Define the importer
PassmanImporter.lastpassCsv = {
	info: {
		name: 'LastPass csv',
		id: 'lastpassCsv',
		description: 'Create an csv export. Go to More options -> Advanced -> Export -> Last Pass CSV File'
	}
};

PassmanImporter.lastpassCsv.readFile = function (file_data) {
	var parsed_csv = PassmanImporter.readCsv(file_data);
	var credential_list = [];
	for (var i = 0; i < parsed_csv.length; i++) {
		var row = parsed_csv[i];
		var _credential = PassmanImporter.newCredential();
		_credential.label = row.name;
		_credential.username = row.username;
		_credential.password = row.password;
		_credential.url = row.url;
		_credential.tags = [{text: row.grouping}];
		_credential.description = row.extra;
		credential_list.push(_credential);
	}
	return credential_list;
};