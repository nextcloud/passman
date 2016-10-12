// Importers should always start with this
if (!window['PassmanImporter']) {
	var PassmanImporter = {}
}
// Define the importer
PassmanImporter.passmanJson = {
	info: {
		name: 'Passman JSON',
		id: 'passmanJson',
		description: 'Export the item in passman as passman json, with all fields enabled'
	}
};

PassmanImporter.passmanJson.readFile = function (file_data) {
	return new C_Promise(function(){
		var parsed_json = PassmanImporter.readJson(file_data);
		var credential_list = [];
		for (var i = 0; i < parsed_json.length; i++) {
			var item = parsed_json[i];
			var _credential = PassmanImporter.newCredential();
			_credential.label = item.label;
			_credential.username = item.account;
			_credential.password = item.password;
			_credential.email = item.email;
			_credential.url = item.url;
			_credential.tags = item.tags;
			//Check for custom fields
			if (item.hasOwnProperty('customFields')) {
				//Check for otp
				if (item.customFields.length > 0) {
					for (var cf = 0; cf < item.customFields.length; cf++) {
						_credential.custom_fields.push(
							{
								'label': item.customFields[cf].label,
								'value': item.customFields[cf].value,
								'secret': (item.customFields[cf].clicktoshow == '1')
							}
						)
					}
				}
			}
			if (item.hasOwnProperty('otpsecret')) {
				if (item.otpsecret) {
					_credential.otp = {
						'issuer': item.otpsecret.issuer,
						'label': item.otpsecret.label,
						'qr_uri': {
							'image': item.otpsecret.qrCode,
							'qrData': ''
						},
						'secret': item.otpsecret.secret,
						'type': item.otpsecret.type
					}
				}
			}
			if(_credential.label){
				credential_list.push(_credential);
			}
			var progress = {
				percent: i/parsed_json.length*100,
				loaded: i,
				total: parsed_json.length
			};

			this.call_progress(progress);
		}
		this.call_then(credential_list);
	});
};