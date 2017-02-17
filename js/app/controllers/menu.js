/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function () {
	'use strict';


	/**
	 * @ngdoc function
	 * @name passmanApp.controller:MenuCtrl
	 * @description
	 * # MenuCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('MenuCtrl', ['$scope', 'VaultService', '$location', '$rootScope', 'TagService','SettingsService',
			function ($scope, VaultService, $location, $rootScope, TagService, SettingsService) {
				$rootScope.logout = function () {
					SettingsService.setSetting('defaultVaultPass', false);
					TagService.resetTags();
					$rootScope.$broadcast('logout');
					$location.path('/');
				};

				$scope.selectedTags = [];
				$scope.getTags = function ($query) {
					return TagService.searchTag($query);
				};

				$scope.$watch(function () {
					return VaultService.getActiveVault();
				}, function (vault) {
					$scope.active_vault = vault;
				});

				$scope.filtered_tags = [];
				$rootScope.$on('limit_tags_in_list', function (evt, tags) {
					$scope.filtered_tags = [];
					for (var i = 0; i < tags.length; i++) {
						var tag = {
							text: tags[i]
						};

						var found = false;
						for (var x = 0; x < $scope.selectedTags.length; x++) {
							if ($scope.selectedTags[x].text === tag.text) {
								found = true;
							}
						}
						if (found === false) {
							$scope.filtered_tags.push(tag);
						}

					}
				});

				$scope.$watch('selectedTags', function () {
					$rootScope.$broadcast('selected_tags_updated', $scope.selectedTags);
				}, true);

				$scope.tagClicked = function (tag) {
					$scope.selectedTags.push(tag);
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
}());