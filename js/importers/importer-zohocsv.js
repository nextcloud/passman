// Importers should always start with this
if (!window['PassmanImporter']) {
	var PassmanImporter = {}
}
// Define the importer
PassmanImporter.zohoCsv = {
	info: {
		name: 'ZOHO csv',
		id: 'zohoCsv',
		description: 'Create an csv export. Go to Tools ->  Export secrets -> Select "General CSV" and click "Export Secrets"'
	}
};

PassmanImporter.zohoCsv.readFile = function (file_data) {
	var parsed_csv = PassmanImporter.readCsv(file_data, false);
	var credential_list = [];
	for (var i = 0; i < parsed_csv.length; i++) {
		var row = parsed_csv[i];
		var _credential = PassmanImporter.newCredential();
		_credential.label = row[0];
		_credential.username = row[3];
		_credential.password = row[4];
		_credential.url = row[1];
		_credential.description = row[2];
		if(_credential.label){
			credential_list.push(_credential);
		}
	}
	return credential_list;
};