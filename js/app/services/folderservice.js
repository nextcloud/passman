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
 * @ngdoc service
 * @name passmanApp.CacheService
 * @description
 * # CacheService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('FolderService', [function () {
		return {
			expandWithFolder: function ($scope, CredentialList) {

			$scope.currentFolder = "/";
			$scope.FolderList = ["/"];
			$scope.TempFolderList = [];
			$scope.BreadcrumbList = [];

			$scope.buildFolderList = function (update) {

				$scope.FolderList = ["/"];
				$scope.TempFolderList = [];
				$scope.BreadcrumbList = [];

				for (var i = 0; i < CredentialList.length; i++) {
					var _credential = CredentialList[i];

					if (_credential.folderpath !== null) {
						while(_credential.folderpath.includes('//')){
							_credential.folderpath=_credential.folderpath.replace("//", "/");
						}
						console.log(_credential.folderpath);
						if (String(_credential.folderpath).startsWith(String($scope.currentFolder)) || update) {
							if ($scope.FolderList.indexOf(_credential.folderpath) <= -1) {
								$scope.FolderList.push(_credential.folderpath);
							}
						} else {
							_credential.folderpath = "/";
						}
					} else {
						_credential.folderpath = "/";
					}
					CredentialList[i] = _credential;
				}

				if (!$scope.FolderList.includes($scope.currentFolder)) {
					$scope.currentFolder = "/";
				}

			};

			$scope.createBreadCrumbList = function () {
				var array = $scope.currentFolder.split("/").filter(Boolean);
				var res = [];
				var fullPath = "/";
				array.forEach(function (element) {
					fullPath += element + "/";
					res.push({fullPath: fullPath, name: element});
				});
				$scope.BreadcrumbList = res;

			};

			$scope.setCurrentFolder = function (folder) {
				$scope.currentFolder = folder;
				$scope.createBreadCrumbList();
			};

			$scope.setCurrentFolderFromBreadcrumb = function (folder) {
				$scope.currentFolder = folder;
				$scope.createBreadCrumbList();
				$scope.getCurrentFolderList();
			};

			$scope.setCurrentFolderFromUI = function (folder) {
				var c = $scope.currentFolder + folder + "/";//$scope.cutScopeFolderFromFoldername(folder);
				$scope.setCurrentFolder(c);
				$scope.getCurrentFolderList();
			};

			$scope.checkIfCurrentFolderIsSelected = function (folder) {
				if ($scope.currentFolder === "/" && "/" === folder) {
					return true;
				}
				return $scope.currentFolder.substring(0, $scope.currentFolder.length - 1) === folder;
			};

			$scope.checkIfFolderIsSubfolder = function (folder) {
				return folder.startsWith($scope.currentFolder);
			};

			$scope.cutScopeFolderFromFoldername = function (folder) {
				var withoutParent = folder.replace($scope.currentFolder, "") + "/";
				var withoutRest = "";
				var temp = "";
				if (withoutParent.startsWith("/")) {
					temp = withoutParent.substring(1, withoutParent.length);
					withoutRest = temp.substring(0, temp.indexOf("/"));
				} else {
					withoutRest = withoutParent.substring(0, withoutParent.indexOf("/"));
				}

				return withoutRest;
			};

			$scope.getCurrentFolderList = function () {
				var Temp = [];
				for (var i = 0; i < $scope.FolderList.length; i++) {
					//console.log("test: "+$scope.FolderList[i]);
					if ($scope.FolderList[i].startsWith($scope.currentFolder) && $scope.checkIfFolderIsSubfolder($scope.FolderList[i])) {
						var reducedFoldername = $scope.cutScopeFolderFromFoldername($scope.FolderList[i]);
						if (Temp.indexOf(reducedFoldername) <= -1) {
							Temp.push(reducedFoldername);
						}
					}
				}
				$scope.TempFolderList = Temp;
			};

		},
		};
	}]);
}());