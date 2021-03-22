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
	 * @name passmanApp.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('CredentialCtrl', ['$scope', 'VaultService', 'SettingsService', '$location', 'CredentialService',
			'$rootScope', 'FileService', 'EncryptService', 'TagService', '$timeout', 'NotificationService', 'CacheService', 'ShareService', 'SharingACL', '$interval', '$filter', '$routeParams', '$sce', '$translate',
			function ($scope, VaultService, SettingsService, $location, CredentialService, $rootScope, FileService, EncryptService, TagService, $timeout, NotificationService, CacheService, ShareService, SharingACL, $interval, $filter, $routeParams, $sce, $translate) {
				$scope.active_vault = VaultService.getActiveVault();
				if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
					if (!$scope.active_vault) {
						$location.path('/');
					}
				} else {
					if (SettingsService.getSetting('defaultVault') && SettingsService.getSetting('defaultVaultPass')) {
						var _vault = angular.copy(SettingsService.getSetting('defaultVault'));
						_vault.vaultKey = angular.copy(SettingsService.getSetting('defaultVaultPass'));
						VaultService.setActiveVault(_vault);
						$scope.active_vault = _vault;
						//@TODO check if vault exists
					}
				}

				$scope.show_spinner = true;
				var fetchCredentials = function () {
					VaultService.getVault({guid: $routeParams.vault_id}).then(function (vault) {
						var vaultKey = angular.copy($scope.active_vault.vaultKey);
						var _credentials = angular.copy(vault.credentials);
						vault.credentials = [];
						$scope.active_vault = vault;
						$scope.active_vault.vaultKey = vaultKey;
						if(!$rootScope.vaultCache){
              				$rootScope.vaultCache = [];
						}
						VaultService.setActiveVault($scope.active_vault);
						for (var i = 0; i < _credentials.length; i++) {
							var _credential = _credentials[i];
							try {
								if (!_credential.shared_key) {
									_credential = CredentialService.decryptCredential(angular.copy(_credential));

								} else {
									var enc_key = EncryptService.decryptString(_credential.shared_key);
									_credential = ShareService.decryptSharedCredential(angular.copy(_credential), enc_key);
								}
								_credential.tags_raw = _credential.tags;
							} catch (e) {

								NotificationService.showNotification($translate.instant('error.decrypt'), 5000);
								//$rootScope.$broadcast('logout');
								//SettingsService.setSetting('defaultVaultPass', null);
								//.setSetting('defaultVault', null);
								//$location.path('/')

							}
							_credentials[i] = _credential;
						}

						ShareService.getCredendialsSharedWithUs(vault.guid).then(function (shared_credentials) {
							for (var c = 0; c < shared_credentials.length; c++) {
								var _shared_credential = shared_credentials[c];
								var decrypted_key = EncryptService.decryptString(_shared_credential.shared_key);
								var _shared_credential_data;
								try {
									_shared_credential_data = ShareService.decryptSharedCredential(_shared_credential.credential_data, decrypted_key);
								} catch (e) {

								}
								if (_shared_credential_data) {
									delete _shared_credential.credential_data;
									_shared_credential_data.acl = _shared_credential;
									_shared_credential_data.acl.permissions = new SharingACL(_shared_credential_data.acl.permissions);
									_shared_credential_data.tags_raw = _shared_credential_data.tags;
									_credentials.push(_shared_credential_data);
								}
							}
							angular.merge($scope.active_vault.credentials, _credentials);
							$scope.show_spinner = false;
							$rootScope.$broadcast('credentials_loaded');
							$rootScope.vaultCache[$scope.active_vault.guid] = angular.copy($scope.active_vault);
							if(!vault.private_sharing_key){
								var key_size = 1024;
								ShareService.generateRSAKeys(key_size).then(function (kp) {
									var pem = ShareService.rsaKeyPairToPEM(kp);
									$scope.creating_keys = false;
									$scope.active_vault.private_sharing_key = pem.privateKey;
									$scope.active_vault.public_sharing_key = pem.publicKey;
									$scope.$digest();
									VaultService.updateSharingKeys($scope.active_vault);
								});
							}
							$scope.checkURLAction();
						});
					});
				};

				var getPendingShareRequests = function () {
					ShareService.getPendingRequests().then(function (shareRequests) {
						if (shareRequests.length > 0) {
							$scope.incoming_share_requests = shareRequests;
							jQuery('.share_popup').dialog({
								width: 800,
								modal: true,
								dialogClass: 'shareincoming-dialog'
							});
						}
					});
				};

				var refresh_data_interval = null;
				if ($scope.active_vault) {
					$scope.$parent.selectedVault = true;
					if($rootScope.vaultCache && $rootScope.vaultCache[$scope.active_vault.guid]){
            			$scope.active_vault = $rootScope.vaultCache[$scope.active_vault.guid];
            			$rootScope.$broadcast('credentials_loaded');
            			$scope.show_spinner = false;
          			} else {
            			fetchCredentials();
          			}
					getPendingShareRequests();
					refresh_data_interval = $interval(function () {
						fetchCredentials();
						getPendingShareRequests();
					}, 60000 * 5);
				}
				
				$scope.$on('$destroy', function () {
					$interval.cancel(refresh_data_interval);
				});


				$scope.permissions = new SharingACL(0);

				$scope.hasPermission = function (acl, permission) {
					if (acl) {
						var tmp = new SharingACL(acl.permission);
						return tmp.hasPermission(permission);
					} else {
						return true;
					}

				};

				$scope.acceptShareRequest = function (share_request) {
					var crypted_shared_key = share_request.shared_key;
					var private_key = EncryptService.decryptString(VaultService.getActiveVault().private_sharing_key);

					private_key = ShareService.rsaPrivateKeyFromPEM(private_key);
					/** global: forge */
					crypted_shared_key = private_key.decrypt(forge.util.decode64(crypted_shared_key));
					crypted_shared_key = EncryptService.encryptString(crypted_shared_key);

					ShareService.saveSharingRequest(share_request, crypted_shared_key).then(function () {
						var idx = $scope.incoming_share_requests.indexOf(share_request);
						$scope.incoming_share_requests.splice(idx, 1);
						var active_share_requests = false;
						for (var v = 0; v < $scope.incoming_share_requests.length; v++) {
							if ($scope.incoming_share_requests[v].target_vault_id === $scope.active_vault.vault_id) {
								active_share_requests = true;
							}
						}
						if (active_share_requests === false) {
							jQuery('.ui-dialog').remove();
							fetchCredentials();
						}
					});
				};

				$scope.declineShareRequest = function (share_request) {
					ShareService.declineSharingRequest(share_request).then(function () {
						var idx = $scope.incoming_share_requests.indexOf(share_request);
						$scope.incoming_share_requests.splice(idx, 1);
						var active_share_requests = false;
						for (var v = 0; v < $scope.incoming_share_requests.length; v++) {
							if ($scope.incoming_share_requests[v].target_vault_id === $scope.active_vault.vault_id) {
								active_share_requests = true;
							}
						}
						if (active_share_requests === false) {
							jQuery('.ui-dialog').remove();
							fetchCredentials();
						}
					});
				};



				var settingsLoaded = function () {
					$scope.settings = SettingsService.getSettings();
				};

				if(!SettingsService.getSetting('settings_loaded')){
					$rootScope.$on('settings_loaded', function () {
						settingsLoaded();
					});
				} else {
					settingsLoaded();
				}


				$scope.addCredential = function () {
					var new_credential = CredentialService.newCredential();
					var enc_c = CredentialService.encryptCredential(new_credential);
					SettingsService.setSetting('edit_credential', enc_c);
					$location.path('/vault/' + $scope.active_vault.guid + '/new');
				};

				$scope.editCredential = function (credential) {
					var _credential = angular.copy(credential);
					$rootScope.$emit('app_menu', false);
					SettingsService.setSetting('edit_credential', CredentialService.encryptCredential(_credential));
					$location.path('/vault/' + $scope.active_vault.guid + '/edit/' + _credential.guid);
				};

				$scope.getRevisions = function (credential) {
					var _credential = angular.copy(credential);
					$rootScope.$emit('app_menu', false);
					SettingsService.setSetting('revision_credential', CredentialService.encryptCredential(_credential));
					$location.path('/vault/' + $scope.active_vault.guid + '/' + _credential.guid + '/revisions');
				};

				$scope.shareCredential = function (credential) {
					var _credential = angular.copy(credential);
					$rootScope.$emit('app_menu', false);
					SettingsService.setSetting('share_credential', CredentialService.encryptCredential(_credential));
					$location.path('/vault/' + $scope.active_vault.guid + '/' + _credential.guid + '/share');
				};

				var notification;
				$scope.deleteCredential = function (credential) {
					var _credential = angular.copy(credential);
					try {
						_credential = CredentialService.decryptCredential(_credential);
					} catch (e) {

					}
					_credential.delete_time = new Date().getTime() / 1000;
					for (var i = 0; i < $scope.active_vault.credentials.length; i++) {
						if ($scope.active_vault.credentials[i].credential_id === credential.credential_id) {
							$scope.active_vault.credentials[i].delete_time = _credential.delete_time;
						}
					}
					$scope.closeSelected();
					if (notification) {
						NotificationService.hideNotification(notification);
					}
					var key = CredentialService.getSharedKeyFromCredential(_credential);
					CredentialService.updateCredential(_credential, false, key).then(function () {
						notification = NotificationService.showNotification($translate.instant('credential.deleted'), 5000);
					});
				};

				$scope.recoverCredential = function (credential) {
					var _credential = angular.copy(credential);
					try {
						_credential = CredentialService.decryptCredential(_credential);
					} catch (e) {

					}
					for (var i = 0; i < $scope.active_vault.credentials.length; i++) {
						if ($scope.active_vault.credentials[i].credential_id === credential.credential_id) {
							$scope.active_vault.credentials[i].delete_time = 0;
						}
					}
					_credential.delete_time = 0;
					$scope.closeSelected();
					if (notification) {
						NotificationService.hideNotification(notification);
					}
					var key = CredentialService.getSharedKeyFromCredential(_credential);
					CredentialService.updateCredential(_credential, false, key).then(function () {
						NotificationService.showNotification($translate.instant('credential.recovered'), 5000);
					});
				};

				$scope.destroyCredential = function (credential) {
					var _credential = angular.copy(credential);
					CredentialService.destroyCredential(_credential.guid).then(function () {
						for (var i = 0; i < $scope.active_vault.credentials.length; i++) {
							if ($scope.active_vault.credentials[i].credential_id === credential.credential_id) {
								$scope.active_vault.credentials.splice(i, 1);
								NotificationService.showNotification($translate.instant('credential.destroyed'), 5000);
								break;
							}
						}
					});
				};

				$scope.view_mode = 'list'; //@TODO make this a setting
				$scope.switchViewMode = function (viewMode) {
					$scope.view_mode = viewMode;
				};

                $rootScope.$on('push_decrypted_credential_to_list', function () {
                    $rootScope.$broadcast('credentials_loaded');
                });

				$scope.filterOptions = {
					filterText: '',
					fields: ['label', 'username', 'email', 'custom_fields']
				};

                //searchboxfix
                $scope.$on('nc_searchbox', function(event, searchterm, fields) {
                    $scope.filterOptions.filterText=searchterm;

                    if(fields){
						$scope.filterOptions.fields=fields;
					}
				});

                $scope.filtered_credentials = [];
                $scope.$watch('[selectedtags, filterOptions, delete_time, active_vault.credentials]', function () {
                    if (!$scope.active_vault) {
                        return;
                    }
                    if ($scope.active_vault.credentials) {
                        var credentials = angular.copy($scope.active_vault.credentials);
                        var filtered_credentials = $filter('credentialSearch')(credentials, $scope.filterOptions);
                        filtered_credentials = $filter('tagFilter')(filtered_credentials, $scope.selectedtags);
                        filtered_credentials = $filter('filter')(filtered_credentials, {hidden: 0});
                        $scope.filtered_credentials = filtered_credentials;
                        $scope.filterOptions.selectedtags = angular.copy($scope.selectedtags);
                        for (var i = 0; i < $scope.active_vault.credentials.length; i++) {
                            var _credential = $scope.active_vault.credentials[i];
                            if (_credential.tags) {
                                TagService.addTags(_credential.tags);
                            }
                        }
                    }
                }, true);

                $scope.no_credentials_label=[];
                $scope.no_credentials_label.all=true;
                $scope.no_credentials_label.s_good=false;
                $scope.no_credentials_label.s_medium=false;
                $scope.no_credentials_label.s_low=false;
                $scope.no_credentials_label.expired=false;

                $scope.disableAllLabels = function(){
                    $scope.no_credentials_label.all=false;
                    $scope.no_credentials_label.s_good=false;
                    $scope.no_credentials_label.s_medium=false;
                    $scope.no_credentials_label.s_low=false;
                    $scope.no_credentials_label.expired=false;
				};

                //watch for special tags
                $scope.$on('filterSpecial', function(event, args) {

                    $scope.disableAllLabels();
                    switch (args) {
                        case "strength_good":
                        	$scope.filterStrength(3,1000);
                            $scope.no_credentials_label.s_good=true;
                        	break;
                        case "strength_medium":
                        	$scope.filterStrength(2,3);
                            $scope.no_credentials_label.s_medium=true;
                        	break;
                        case "strength_low":
                        	$scope.filterStrength(0,1);
                            $scope.no_credentials_label.s_low=true;
                        	break;
                        case "expired":
                        	$scope.filterExpired();
                            $scope.no_credentials_label.expired=true;
                        	break;
                        case "all":
                        	$scope.filterAll();
                            $scope.no_credentials_label.all=true;
                        	break;
                    }
                });

                $scope.getListSizes = function(){
                	var l = $scope.filtered_credentials;

                	var deleted=0;
                    for (var i = 0; i < l.length; i++) {
						if(l[i].delete_time>0){
							deleted++;
						}
                    }

                    var result=[];
                    result.listsize=l.length;
                    result.listsize_wout_deleted=l.length-deleted;
                    result.listsize_deleted=deleted;

                	return result;
				};

                $scope.filterAll = function(){
                    $scope.selectedtags=[];
                    $scope.filterOptions.filterText="";
                    var creds_filtered=[];

                    for (var i = 0; i < $scope.active_vault.credentials.length; i++) {
                        if($scope.active_vault.credentials[i].delete_time===0){
                            creds_filtered.push($scope.active_vault.credentials[i]);
                        }
                    }

					$scope.filtered_credentials=$scope.filterHidden(creds_filtered);
                };

                $scope.filterStrength = function(strength_min, strength_max){
                    var initialCredentials=$scope.active_vault.credentials;
                    var postFiltered=[];
                    for (var i = 0; i < initialCredentials.length; i++) {
                        var _credential = initialCredentials[i];
                        var zxcvbn_result = zxcvbn(_credential.password);

                        if(zxcvbn_result.score>=strength_min && zxcvbn_result.score<=strength_max){
                            postFiltered.push(initialCredentials[i]);
                        }
                    }
                    $scope.filtered_credentials=$scope.filterHidden(postFiltered);
                };

                $scope.filterExpired = function(){
                    var initialCredentials=$scope.active_vault.credentials;
                    var now = Date.now();
                    var postFiltered=[];

                    for (var i = 0; i < initialCredentials.length; i++) {
                        var _credential = initialCredentials[i];

                        if(_credential.expire_time!==0 && _credential.expire_time <= now){
                            postFiltered.push(initialCredentials[i]);
                        }
                    }
                    $scope.filtered_credentials=$scope.filterHidden(postFiltered);
                };

                $scope.filterHidden = function(list){
                    var list_without_hidden=[];
                    for (var i = 0; i < list.length; i++) {
                        if(list[i].hidden!==1){
                            list_without_hidden.push(list[i]);
                        }
                    }
                    return list_without_hidden;
                };



                $scope.selectedtags = [];
                var to;
                $rootScope.$on('selected_tags_updated', function (evt, _sTags) {
                    var _selectedTags = [];
                    for (var x = 0; x < _sTags.length; x++) {
                        _selectedTags.push(_sTags[x].text);
                    }
                    $scope.selectedtags = _selectedTags;
                    $timeout.cancel(to);
                    if (_selectedTags.length > 0) {
                        to = $timeout(function () {
                            if ($scope.filtered_credentials) {
                                var _filtered_tags = [];
                                for (var i = 0; i < $scope.filtered_credentials.length; i++) {
                                    var tags = $scope.filtered_credentials[i].tags_raw;
                                    for (var x = 0; x < tags.length; x++) {
                                        var tag = tags[x].text;
                                        if (_filtered_tags.indexOf(tag) === -1) {
                                            _filtered_tags.push(tag);
                                        }
                                    }
                                }

                                $rootScope.$emit('limit_tags_in_list', _filtered_tags);
                            }
                        }, 50);
                    }
                });

                $scope.delete_time = 0;
                $scope.showCredentialRow = function (credential) {
                    if ($scope.delete_time === 0) {
                        return credential.delete_time === 0;
                    } else {
                        return credential.delete_time > $scope.delete_time;
                    }

                };

                $rootScope.$on('set_delete_time', function (event, time) {
                    $scope.delete_time = time;
                });

                $scope.setDeleteTime = function (delete_time) {
                    $scope.delete_time = delete_time;
                };

                $scope.selectedCredential = false;
                $scope.selectCredential = function (credential) {
                    if (credential.description) {
                        credential.description_html = $sce.trustAsHtml(angular.copy(credential.description).replace("\n", '<br />'));
                    }
                    $scope.selectedCredential = angular.copy(credential);
                    $rootScope.$emit('app_menu', true);
                };

                $scope.closeSelected = function () {
                    $rootScope.$emit('app_menu', false);
                    $scope.selectedCredential = false;
                };

                $rootScope.$on('logout', function () {
                    if ($scope.active_vault) {
                        $rootScope.vaultCache[$scope.active_vault.guid] = null;
                    }
                    $scope.active_vault = null;
                    $scope.credentials = [];
                    VaultService.clearVaultService();
                });

                $scope.$watch(function(){ return $location.search(); }, function(params){
                    $scope.checkURLAction();
                });

                $scope.checkURLAction = function () {
                    var search = $location.search();
                    if (search.show !== undefined && $scope.active_vault.credentials !== undefined &&
						$scope.active_vault.credentials.length > 0) {
                        $scope.closeSelected();
                        $scope.active_vault.credentials.forEach(function(credential, index, myArray) {
                            if (credential.guid === search.show) {
                                $scope.selectCredential(credential);
                                return true;
                            }
                        });
                    }
                };

		$scope.clearState = function () {
			$scope.delete_time = 0;
		};

	}]);
}());
