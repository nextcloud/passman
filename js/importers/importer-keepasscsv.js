// Importers should always start with this
if (!window['PassmanImporter']) {
	var PassmanImporter = {}
}
// Define the importer
PassmanImporter.keepassCsv = {
	info: {
		name: 'KeePass csv',
		id: 'keepassCsv',
		description: 'Create an csv export with the following options enabled: http://i.imgur.com/CaeTA4d.png'
	}
};

PassmanImporter.keepassCsv.readFile = function (file_data, callback) {
	var p = new C_Promise(function(){
		var parsed_csv = PassmanImporter.readCsv(file_data);
		var credential_list = [];
		for (var i = 0; i < parsed_csv.length; i++) {
			var row = parsed_csv[i];
			var _credential = PassmanImporter.newCredential();
			_credential.label = row.account;
			_credential.username = row.login_name;
			_credential.password = row.password;
			_credential.url = row.web_site;
			if (row.hasOwnProperty('expires')) {
				row.expires = row.expires.replace('"','');
				_credential.expire_time = new Date(row.expires).getTime() / 1000;
			}
			var tags = [{text: row.group}];
			if (row.hasOwnProperty('group_tree')) {
				var exploded_tree = row.group_tree.split('\\\\');
				for (var t = 0; t < exploded_tree.length; t++) {
					tags.push({text: exploded_tree[t]});
				}
			}
			_credential.tags = tags;
			credential_list.push(_credential);
			this.call_progress(i/parsed_csv.length*100);
		}
		this.call_then(credential_list);
	});
	return p;
};