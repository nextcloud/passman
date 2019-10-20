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
	 * @name passmanApp.controller:RevisionCtrl
	 * @description
	 * # RevisionCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('RevisionCtrl', ['$scope', 'SettingsService', 'VaultService', 'CredentialService', '$location', '$routeParams', '$rootScope', 'NotificationService', '$filter', 'ShareService', 'EncryptService', '$translate',
			function ($scope, SettingsService, VaultService, CredentialService, $location, $routeParams, $rootScope, NotificationService, $filter, ShareService, EncryptService, $translate) {
				$scope.active_vault = VaultService.getActiveVault();
				if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
					if (!$scope.active_vault) {
						$location.path('/');
					}
				} else {
					if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
						var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
						_vault.vaultKey = SettingsService.getSetting('defaultVaultPass');
						VaultService.setActiveVault(_vault);
						VaultService.getVault(_vault).then(function (vault) {
							vault.vaultKey = SettingsService.getSetting('defaultVaultPass');
							VaultService.setActiveVault(vault);
							$scope.active_vault = vault;
							$scope.$parent.selectedVault = true;
						});
					}
				}

				if ($scope.active_vault) {
					$scope.$parent.selectedVault = true;
				}
				var storedCredential = SettingsService.getSetting('revision_credential');

				var getRevisions = function () {
					CredentialService.getRevisions($scope.storedCredential.guid).then(function (revisions) {
						$scope.revisions = revisions;
					});
				};

				if (!storedCredential) {
					CredentialService.getCredential($routeParams.credential_id).then(function (result) {
						$scope.storedCredential = CredentialService.decryptCredential(angular.copy(result));
						getRevisions();
					});
				} else {
					$scope.storedCredential = CredentialService.decryptCredential(angular.copy(storedCredential));
					getRevisions();
				}

				$scope.selectRevision = function (revision) {
					var key;
					$scope.selectedRevision = angular.copy(revision);

					if (!$scope.storedCredential.hasOwnProperty('acl') && $scope.storedCredential.hasOwnProperty('shared_key')) {
						if ($scope.storedCredential.shared_key) {
							key = EncryptService.decryptString(angular.copy($scope.storedCredential.shared_key));
						}
					}
					if ($scope.storedCredential.hasOwnProperty('acl')) {
						key = EncryptService.decryptString(angular.copy($scope.storedCredential.acl.shared_key));
					}

					if (key) {
						$scope.selectedRevision.credential_data = ShareService.decryptSharedCredential(angular.copy(revision.credential_data), key);
					} else {
						$scope.selectedRevision.credential_data = CredentialService.decryptCredential(angular.copy(revision.credential_data));
					}

					$rootScope.$emit('app_menu', true);
				};

				$scope.closeSelected = function () {
					$rootScope.$emit('app_menu', false);
					$scope.selectedRevision = false;
				};

				$scope.deleteRevision = function (revision) {
					CredentialService.deleteRevision($scope.storedCredential.guid, revision.revision_id).then(function () {
						for (var i = 0; i < $scope.revisions.length; i++) {
							if ($scope.revisions[i].revision_id === revision.revision_id) {
								$scope.revisions.splice(i, 1);
								NotificationService.showNotification($translate.instant('revision.deleted'), 5000);
								break;
							}
						}
					});
				};

				$scope.restoreRevision = function (revision) {
					var key;
					var _revision = angular.copy(revision);
					var _credential = _revision.credential_data;

					if (!$scope.storedCredential.hasOwnProperty('acl') && $scope.storedCredential.hasOwnProperty('shared_key')) {
						if ($scope.storedCredential.shared_key) {
							key = EncryptService.decryptString(angular.copy($scope.storedCredential.shared_key));
						}
					}
					if ($scope.storedCredential.hasOwnProperty('acl')) {
						key = EncryptService.decryptString(angular.copy($scope.storedCredential.acl.shared_key));
					}
					if (key) {
						_credential = ShareService.encryptSharedCredential(_credential, key);
					}
					delete _credential.shared_key;

					//Used in activity
					_credential.revision_created = $filter('date')(_revision.created * 1000, "dd-MM-yyyy @ HH:mm:ss");
					CredentialService.updateCredential(_credential, (key)).then(function (restored_cred) {
						SettingsService.setSetting('revision_credential', null);
						$rootScope.$emit('app_menu', false);
						$location.path('/vault/' + $routeParams.vault_id);
						NotificationService.showNotification($translate.instant('revision.restored'), 5000);

                        $scope.updateExistingListWithCredential(restored_cred);
					});
				};

                $scope.updateExistingListWithCredential = function (credential) {
                    try {
                        if (!credential.shared_key) {
                            credential = CredentialService.decryptCredential(credential);
                        } else {
                            var enc_key = EncryptService.decryptString(credential.shared_key);
                            credential = ShareService.decryptSharedCredential(credential, enc_key);
                        }
                        credential.tags_raw = credential.tags;


                        var found=false;
                        var credList=$rootScope.vaultCache[$scope.active_vault.guid].credentials;
                        for (var i = 0; i < credList.length; i++) {
                            if (credList[i].credential_id === credential.credential_id) {
                                $rootScope.vaultCache[$scope.active_vault.guid].credentials[i]=credential;
                                found=true;
                            }
                        }

                        if(!found){
                            $rootScope.vaultCache[$scope.active_vault.guid].credentials.push(credential);
                        }
                        $rootScope.$broadcast('push_decrypted_credential_to_list', credential);

                    } catch (e) {
                        NotificationService.showNotification($translate.instant('error.decrypt'), 5000);
                        console.log(e);
                    }
                };

				$scope.cancelRevision = function () {
					$location.path('/vault/' + $routeParams.vault_id);
					$scope.storedCredential = null;
					SettingsService.setSetting('revision_credential', null);
				};

			}]);

}());