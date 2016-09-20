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

			$scope.selectedTags = [];
			$scope.getTags = function ($query) {
				console.log(TagService.searchTag($query));
				return TagService.searchTag($query);
			};

			$scope.filtered_tags = [];
			$rootScope.$on('limit_tags_in_list', function (evt, tags) {
				$scope.filtered_tags = [];
				for (var i = 0; i < tags.length; i++) {
					var tag = {
						text: tags[i]
					};

					var found = false;
					for (var x = 0; x < $scope.selectedTags.length; x++) {
						if($scope.selectedTags[x].text === tag.text){
							found = true;
						}
					}
					if(found === false){
						$scope.filtered_tags.push(tag);
					}

				}
			});

			$scope.$watch('selectedTags', function () {
				$rootScope.$broadcast('selected_tags_updated', $scope.selectedTags)
			}, true);

			$scope.tagClicked = function (tag) {
				$scope.selectedTags.push(tag)
			};

			$scope.available_tags = TagService.getTags();

			$scope.$watch(function () {
				if ($scope.selectedTags.length === 0) {
					return TagService.getTags();
				} else {
					return $scope.filtered_tags;
				}
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
