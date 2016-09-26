// Importers should always start with this
if (!window['PassmanImporter']) {
	var PassmanImporter = {}
}
// Define the importer
PassmanImporter.randomData = {
	info: {
		name: 'Random data',
		id: 'randomData',
		description: 'Create\'s 10 random credentials for testing purposes.'
	}
};

PassmanImporter.randomData.readFile = function (file_data, callback) {
	return new C_Promise(function () {
		var tags =
			['Social media',
				'Hosting',
				'Forums',
				'Webshops',
				'FTP',
				'SSH',
				'Banking',
				'Applications',
				'Server stuff',
				'mysql',
				'Wifi',
				'Games',
				'Certificate',
				'Serials'
			];
		var label;
		var credential_list = [];
		var _this = this;
		var generateCredential = function (max, i, callback) {
			if (jQuery) {
				var url = OC.generateUrl('apps/passman/api/internal/generate_person');
				$.ajax({
					url: url,
					dataType: 'json',
					success: function (data) {
						var _credential = PassmanImporter.newCredential();
						label = (Math.random() >= 0.5) ? data.domain : data.email_d + ' - ' + data.email_u;
						_credential.label = label;
						_credential.username = data.username;
						_credential.password = data.password;
						_credential.url = data.url;

						var tag_amount = Math.floor(Math.random() * 5);
						for (var ta = 0; ta < tag_amount; ta++) {
							var item = tags[Math.floor(Math.random() * tags.length)];
							var tag = {
								text: item
							};
							if (_credential.tags.indexOf(tag) === -1) {
								_credential.tags.push(tag);
							}
						}
						credential_list.push(_credential);
						if (i <= max) {
							var progress = {
								percent: i / max * 100,
								loaded: i,
								total: max
							};
							_this.call_progress(progress);
							generateCredential(max, i + 1, callback)
						} else {
							callback(credential_list)
						}
					}
				});
			}
		};


		generateCredential(10, 1, function (credential_list) {
			_this.call_then(credential_list);
		});
	});
};
