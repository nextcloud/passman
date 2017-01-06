describe('BookmarkletCtrl', function() {
	beforeEach(module('passmanApp'));
	beforeEach(module('LocalStorageModule'));

	var $controller;
	var $scope;
	beforeEach(inject(function(_$controller_){
		// The injector unwraps the underscores (_) from around the parameter names when matching
		$controller = _$controller_;
	}));
	beforeEach(inject(function($rootScope) {
		$scope = $rootScope.$new();
	}));


	describe('Select vault test', function() {
		it('Vault name should match test', function() {
			var controller = $controller('BookmarkletCtrl', { $scope: $scope });
			$scope.selectVault({name: 'test'});
			expect($scope.list_selected_vault).toEqual({name: 'test'});
		});
	});

	describe('Vault logout', function() {
		it('Vault should not be active test', function() {
			var controller = $controller('BookmarkletCtrl', { $scope: $scope });
			$scope.logout();
			expect($scope.active_vault).toEqual(false);
		});
	});

	describe('Clear current state', function() {
		it('Should clear current state', function() {
			var controller = $controller('BookmarkletCtrl', { $scope: $scope });
			$scope.clearState();
			expect($scope.list_selected_vault).toEqual(false);
			expect($scope.creating_vault).toEqual(false);
			expect($scope.error).toEqual(false);
		});
	});

	describe('Create new vault', function() {
		it('Should set creating_vault', function() {
			var controller = $controller('BookmarkletCtrl', { $scope: $scope });
			$scope.newVault();
			expect($scope.creating_vault).toEqual(true);
		});

		it('Should generate private sharing key', function() {
			var controller = $controller('BookmarkletCtrl', { $scope: $scope });
			$scope.newVault();
			setTimeout(function () {
				expect($scope.sharing_keys.private_sharing_key).not.toBeNull();
			}, 5000);
		});

		it('Should generate public sharing key', function() {
			var controller = $controller('BookmarkletCtrl', { $scope: $scope });
			$scope.newVault();
			setTimeout(function () {
				expect($scope.sharing_keys.public_sharing_key).not.toBeNull();
			}, 5000);
		});
	});
});