'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MenuCtrl
 * @description
 * # MenuCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('MenuCtrl', ['$scope', 'VaultService', 'SettingsService', '$location', '$rootScope', 'TagService',
		function ($scope, VaultService, SettingsService, $location, $rootScope, TagService) {
			$scope.logout = function () {
				SettingsService.setSetting('defaultVaultPass', false);
				$rootScope.$broadcast('logout');
				$location.path('/');
			};

			// @TODO Show only tag's that exist in the list (when filtered on tag)

			$scope.selectedTags = [];
			$scope.getTags = function ($query) {
				console.log(TagService.searchTag($query));
				return TagService.searchTag($query);
			};

			$scope.$watch('selectedTags', function () {
				$rootScope.$broadcast('selected_tags_updated', $scope.selectedTags)
			}, true);

			$scope.tagClicked = function (tag) {
				$scope.selectedTags.push(tag)
			};

			$scope.available_tags = TagService.getTags();

			$scope.$watch(function () {
				return TagService.getTags();
			}, function (tags) {
				$scope.available_tags = tags;
			}, true);

			$scope.toggleDeleteTime = function () {
				if ($scope.delete_time > 0) {
					$scope.delete_time = 0;
				} else {
					$scope.delete_time = 1;
				}
				$rootScope.$broadcast('set_delete_time', $scope.delete_time);
			};
		}]);
