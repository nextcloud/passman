'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('MainCtrl', ['$scope', '$rootScope', '$location', function ($scope, $rootScope, $location) {
		$scope.selectedVault = false;

		$scope.http_warning_hidden = true;
		if($location.$$protocol === 'http'){
			$scope.using_http = true;
			$scope.http_warning_hidden = false;

		}

		$rootScope.setHttpWarning = function(state){
			$scope.http_warning_hidden = state;
		};

		$rootScope.$on('app_menu', function(evt, shown){
			$scope.app_sidebar = shown;
		});

		$rootScope.$on('logout', function () {
			$scope.selectedVault = false;
		})
	}]);

