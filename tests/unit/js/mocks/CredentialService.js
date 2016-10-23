angular.module('mock.credentialsService', []).
service('CredentialService', function($q) {
	var credentialService = {};
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
	credentialService.getCredential = function() {
		var _credential = {
			id: 8888,
			label: "test user"
		};
		return $q.when(_credential);
	};

	credentialService.newCredential = function () {
		return angular.copy(credential);
	};

	credentialService.getRevisions = function() {
		var mockRevision = [{
			id: 1234,
			created: ""
		}];
		return $q.when(mockRevision);
	};

	return credentialService;
});