describe('CredentialCtrl', function() {
	var ctrl, scope, rootScope;
	beforeEach(module('passmanApp'));
	beforeEach(module('LocalStorageModule'));
	beforeEach(module('mock.credentialsService'));

	beforeEach(inject(function($controller, $rootScope, _CredentialService_, SettingsService) { // inject mocked service
		scope = $rootScope.$new();
		rootScope = $rootScope;
		ctrl = $controller('CredentialCtrl', {
			$scope: scope,
			CredentialService: _CredentialService_,
			SettingService: SettingsService
		});
	}));

	describe('Test events', function() {
		it('[event] selected_tags_updated', function() {
			rootScope.$broadcast('selected_tags_updated', [{text: 'hello'}]);
			expect(scope.selectedtags).toEqual(['hello']);
		});

		it('[event] set_delete_time', function() {
			rootScope.$broadcast('set_delete_time', 1337);
			expect(scope.delete_time).toEqual(1337);
		});

		it('[event] logout', function() {
			rootScope.$broadcast('logout');
			expect(scope.active_vault).toEqual(null);
			expect(scope.credentials).toEqual([]);
		});

		it('[event] close selected credential', function() {
			var _spy = spyOn(rootScope, '$emit');
			scope.closeSelected();
			expect(_spy).toHaveBeenCalled();
			expect(scope.selectedCredential).toEqual(false);
		});
	});
});