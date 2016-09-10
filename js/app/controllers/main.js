'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('MainCtrl', ['$scope', 'VaultService', function ($scope, VaultService) {
		$scope.selectedVault = false;
	}]);
