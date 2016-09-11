'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MenuCtrl
 * @description
 * # MenuCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('MenuCtrl', ['$scope', 'VaultService', 'SettingsService', '$location', '$rootScope', function ($scope, VaultService, SettingsService, $location, $rootScope) {
		$scope.logout = function () {
			SettingsService.setSetting('defaultVaultPass', false);
			$rootScope.$broadcast('logout');
			$location.path('/');
		}
	}]);
