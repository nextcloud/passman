'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('MainCtrl', ['$scope', '$rootScope', function ($scope, $rootScope) {
		$scope.selectedVault = false;

		$rootScope.$on('app_menu', function(evt, shown){
			$scope.app_sidebar = shown;
		});

		$rootScope.$on('logout', function () {
			$scope.selectedVault = false;
		})
	}]);

