describe('SettingsCtrl', function() {
	var ctrl, scope, rootScope;
	beforeEach(module('passmanApp'));
	beforeEach(module('LocalStorageModule'));
	beforeEach(module('mock.credentialsService'));

	beforeEach(inject(function($controller, $rootScope, _CredentialService_, SettingsService) { // inject mocked service
		scope = $rootScope.$new();
		rootScope = $rootScope;
		ctrl = $controller('SettingsCtrl', {
			$scope: scope,
			CredentialService: _CredentialService_,
			SettingService: SettingsService
		});
	}));
});