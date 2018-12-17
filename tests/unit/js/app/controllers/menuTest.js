describe('MenuCtrl', function() {
	beforeEach(module('passmanApp'));
	beforeEach(module('LocalStorageModule'));
    beforeEach(module('mock.vaultService'));

	var $controller;
	var $scope;
	beforeEach(inject(function(_$controller_, _VaultService_){
		// The injector unwraps the underscores (_) from around the parameter names when matching
		$controller = _$controller_;
        //$controller.VaultService= _VaultService_;
	}));
	beforeEach(inject(function($rootScope) {
		$scope = $rootScope.$new();
	}));


	describe('$scope.selectedTags', function() {
		it('should add a tag to selected tags', function() {
			var controller = $controller('MenuCtrl', { $scope: $scope });
			$scope.tagClicked({text: 'hello'});
			expect($scope.selectedTags).toEqual([{text: 'hello'}]);
		});
	});

	describe('$scope.toggleDeleteTime', function() {
		it('should toggle delete time', function() {
			var controller = $controller('MenuCtrl', { $scope: $scope });
			$scope.delete_time = 0;
			$scope.toggleDeleteTime();
			expect($scope.delete_time).toEqual(1);
			$scope.toggleDeleteTime();
			expect($scope.delete_time).toEqual(0);
		});
	});

	describe('$scope.getTags()', function() {
		var scope, ctrl, service;
		beforeEach(inject(function(TagService) {
			console.log('*** IN INJECT!!***: ', TagService);
			service = TagService;
		}));

		it('should return an empty array', function() {
			var controller = $controller('MenuCtrl', { $scope: $scope });
			expect($scope.getTags()).toEqual([]);

		});
	});
});