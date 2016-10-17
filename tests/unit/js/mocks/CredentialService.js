angular.module('mock.credentialsService', []).
service('CredentialService', function($q) {
	var credentialService = {};

	credentialService.getCredential = function() {
		var _credential = {
			id: 8888,
			label: "test user"
		};
		return $q.when(_credential);
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