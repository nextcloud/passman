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
		'$rootScope', 'FileService', 'EncryptService', 'TagService', '$timeout', 'NotificationService', 'CacheService', 'ShareService', 'SharingACL', '$interval', '$filter',
		function ($scope, VaultService, SettingsService, $location, CredentialService, $rootScope, FileService, EncryptService, TagService, $timeout, NotificationService, CacheService, ShareService, SharingACL, $interval, $filter) {
			$scope.active_vault = VaultService.getActiveVault();
			if (!SettingsService.getSetting('defaultVault') || !SettingsService.getSetting('defaultVaultPass')) {
				if (!$scope.active_vault) {
					$location.path('/')
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
				VaultService.getVault($scope.active_vault).then(function (vault) {

					var vaultKey = angular.copy($scope.active_vault.vaultKey);
					var _credentials = angular.copy(vault.credentials);
					vault.credentials = [];
					$scope.active_vault = vault;
					$scope.active_vault.vaultKey = vaultKey;
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
							NotificationService.showNotification('An error happend during decryption', 5000);
							//$rootScope.$broadcast('logout');
							//SettingsService.setSetting('defaultVaultPass', null);
							//.setSetting('defaultVault', null);
							//$location.path('/')

						}
						if (_credential.tags) {
							TagService.addTags(_credential.tags);
						}
						_credentials[i] = _credential;
					}

					ShareService.getCredendialsSharedWithUs(vault.guid).then(function (shared_credentials) {
						console.log('Shared credentials', shared_credentials);
						for (var c = 0; c < shared_credentials.length; c++) {
							var _shared_credential = shared_credentials[c];
							var decrypted_key = EncryptService.decryptString(_shared_credential.shared_key);
							try {
								var _shared_credential_data = ShareService.decryptSharedCredential(_shared_credential.credential_data, decrypted_key);
							} catch (e) {

							}
							if (_shared_credential_data) {
								delete _shared_credential.credential_data;
								_shared_credential_data.acl = _shared_credential;
								_shared_credential_data.acl.permissions = new SharingACL(_shared_credential_data.acl.permissions);
								_shared_credential_data.tags_raw = _shared_credential_data.tags;
								if (_shared_credential_data.tags) {
									TagService.addTags(_shared_credential_data.tags);
								}
								_credentials.push(_shared_credential_data);
							}
						}
						angular.merge($scope.active_vault.credentials, _credentials);
						$scope.filtered_credentials = angular.copy($scope.active_vault.credentials);
						$scope.show_spinner = false;
					});
				});
			};

			var getPendingShareRequests = function () {
				ShareService.getPendingRequests().then(function (shareRequests) {
					if (shareRequests.length > 0) {
						$scope.incoming_share_requests = shareRequests;
						jQuery('.share_popup').dialog({
							width: 600,
							position: ['center', 90]
						});
					}
				});
			};


			var refresh_data_interval = null;
			if ($scope.active_vault) {
				$scope.$parent.selectedVault = true;
				fetchCredentials();
				getPendingShareRequests();
				refresh_data_interval = $interval(function () {
					fetchCredentials();
					getPendingShareRequests();
				}, 60000 * 5)
			}
			$scope.$on('$destroy', function() {
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
				console.log('Accepted share request', share_request);
				var crypted_shared_key = share_request.shared_key;
				var private_key = EncryptService.decryptString(VaultService.getActiveVault().private_sharing_key);

				private_key = ShareService.rsaPrivateKeyFromPEM(private_key);
				crypted_shared_key = private_key.decrypt(forge.util.decode64(crypted_shared_key));
				crypted_shared_key = EncryptService.encryptString(crypted_shared_key);

				ShareService.saveSharingRequest(share_request, crypted_shared_key).then(function (result) {
					var idx = $scope.incoming_share_requests.indexOf(share_request);
					$scope.incoming_share_requests.splice(idx, 1);
					var active_share_requests = false;
					for (var v = 0; v < $scope.incoming_share_requests.length; v++) {
						if ($scope.incoming_share_requests[v].target_vault_id == $scope.active_vault.vault_id) {
							active_share_requests = true;
						}
					}
					if (active_share_requests === false) {
						jQuery('.ui-dialog').remove();
						fetchCredentials();
					}
					console.log(result)
				})
			};

			$scope.declineShareRequest = function(share_request){
				ShareService.declineSharingRequest(share_request).then(function () {
					var idx = $scope.incoming_share_requests.indexOf(share_request);
					$scope.incoming_share_requests.splice(idx, 1);
					var active_share_requests = false;
					for (var v = 0; v < $scope.incoming_share_requests.length; v++) {
						if ($scope.incoming_share_requests[v].target_vault_id == $scope.active_vault.vault_id) {
							active_share_requests = true;
						}
					}
					if (active_share_requests === false) {
						jQuery('.ui-dialog').remove();
						fetchCredentials();
					}
				})
			};


			$scope.addCredential = function () {
				var new_credential = CredentialService.newCredential();
				var enc_c = CredentialService.encryptCredential(new_credential);
				SettingsService.setSetting('edit_credential', enc_c);
				$location.path('/vault/' + $scope.active_vault.vault_id + '/new')
			};

			$scope.editCredential = function (credential) {
				var _credential = angular.copy(credential);
				$rootScope.$emit('app_menu', false);
				SettingsService.setSetting('edit_credential', CredentialService.encryptCredential(_credential));
				$location.path('/vault/' + $scope.active_vault.vault_id + '/edit/' + _credential.guid)
			};

			$scope.getRevisions = function (credential) {
				var _credential = angular.copy(credential);
				$rootScope.$emit('app_menu', false);
				SettingsService.setSetting('revision_credential', CredentialService.encryptCredential(_credential));
				$location.path('/vault/' + $scope.active_vault.vault_id + '/' + _credential.guid + '/revisions')
			};

			$scope.shareCredential = function (credential) {
				var _credential = angular.copy(credential);
				$rootScope.$emit('app_menu', false);
				SettingsService.setSetting('share_credential', CredentialService.encryptCredential(_credential));
				$location.path('/vault/' + $scope.active_vault.vault_id + '/' + _credential.guid + '/share')
			};

			var notification;
			$scope.deleteCredential = function (credential) {
				var _credential = angular.copy(credential);
				try {
					_credential = CredentialService.decryptCredential(angular.copy(credential));
				} catch (e) {

				}
				_credential.delete_time = new Date().getTime() / 1000;
				for (var i = 0; i < $scope.credentials.length; i++) {
					if ($scope.credentials[i].credential_id == credential.credential_id) {
						$scope.credentials[i].delete_time = _credential.delete_time;
					}
				}
				$scope.closeSelected();
				if (notification) {
					NotificationService.hideNotification(notification);
				}
				notification = NotificationService.showNotification('Credential deleted <a class="undoDelete" data-item-id="' + credential.credential_id + '">[Undo]</a>', 5000,
					function () {
						CredentialService.updateCredential(_credential).then(function (result) {
							if (result.delete_time > 0) {
								notification = false;

							}
						});
					});

			};

			$scope.recoverCredential = function (credential) {
				var _credential = angular.copy(credential);
				try {
					_credential = CredentialService.decryptCredential(angular.copy(credential));
				} catch (e) {

				}
				for (var i = 0; i < $scope.credentials.length; i++) {
					if ($scope.credentials[i].credential_id == credential.credential_id) {
						$scope.credentials[i].delete_time = 0;
					}
				}
				_credential.delete_time = 0;
				$scope.closeSelected();
				if (notification) {
					NotificationService.hideNotification(notification);
				}
				NotificationService.showNotification('Credential recovered <a class="undoRestore" data-item-id="' + credential.credential_id + '">[Undo]</a>', 5000,
					function () {
						CredentialService.updateCredential(_credential).then(function (result) {
							notification = false;

						});
					});

			};

			$scope.destroyCredential = function (credential) {
				var _credential = angular.copy(credential);
				CredentialService.destroyCredential(_credential.credential_id).then(function (result) {
					for (var i = 0; i < $scope.credentials.length; i++) {
						if ($scope.credentials[i].credential_id == credential.credential_id) {
							$scope.credentials.splice(i, 1);
							NotificationService.showNotification('Credential destroyed', 5000);
							break;
						}
					}
				});
			};

			$scope.view_mode = 'list'; //@TODO make this a setting
			$scope.switchViewMode = function (viewMode) {
				$scope.view_mode = viewMode;
			};

			$scope.filterOptions = {
				filterText: '',
				fields: ['label', 'username', 'email', 'password', 'custom_fields']
			};


			$scope.filtered_credentials = [];
			$scope.$watch('[selectedtags, filterOptions, delete_time, show_spinner]', function(){
				var credentials = angular.copy($scope.active_vault.credentials);
				var filtered_credentials = $filter('credentialSearch')(credentials,$scope.filterOptions);
				filtered_credentials = $filter('tagFilter')(filtered_credentials,$scope.selectedtags);
				filtered_credentials = $filter('filter')(filtered_credentials, {hidden: 0});
				$scope.filtered_credentials = filtered_credentials;
			}, true);

			$scope.selectedtags = [];
			var to;
			$rootScope.$on('selected_tags_updated', function (evt, _sTags) {
				var _selectedTags = [];
				for (var x = 0; x < _sTags.length; x++) {
					_selectedTags.push(_sTags[x].text)
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
					}, 50)
				}
			});

			$scope.delete_time = 0;
			$scope.showCredentialRow = function (credential) {
				if ($scope.delete_time == 0) {
					return credential.delete_time == 0
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
				$scope.selectedCredential = angular.copy(credential);
				$rootScope.$emit('app_menu', true);
			};

			$scope.closeSelected = function () {
				$rootScope.$emit('app_menu', false);
				$scope.selectedCredential = false;
			};

			$rootScope.$on('logout', function () {
				console.log('Logout received, clean up');
				$scope.active_vault = null;
				$scope.credentials = [];
//				$scope.$parent.selectedVault = false;

			});


			$scope.downloadFile = function (credential, file) {
				console.log(credential, file);
				var callback = function (result) {
					var key = null;
					if (!result.hasOwnProperty('file_data')) {
						NotificationService.showNotification('Error downloading file, you probably don\'t have enough permissions', 5000);
						return;

					}
					if (!credential.hasOwnProperty('acl') && credential.hasOwnProperty('shared_key')) {
						if (credential.shared_key) {
							key = EncryptService.decryptString(angular.copy(credential.shared_key));
						}
					}
					if (credential.hasOwnProperty('acl')) {
						key = EncryptService.decryptString(angular.copy(credential.acl.shared_key));
					}

					var file_data = EncryptService.decryptString(result.file_data, key);
					download(file_data, escapeHTML(file.filename), file.mimetype)
					//file.mimetype
					//var uriContent = FileService.dataURItoBlob(file_data, file.mimetype), a = document.createElement("a");
					// a.href = uriContent;
					// a.download = escapeHTML(file.filename);
					// var event = document.createEvent("MouseEvents");
					// event.initMouseEvent(
					// 	"click", true, false, window, 0, 0, 0, 0, 0
					// 	, false, false, false, false, 0, null
					// );
					// window.URL.revokeObjectURL(uriContent);
					// a.dispatchEvent(event);
					// jQuery('#downloadLink').remove();
					setTimeout(function () {
						$scope.selectedCredential = credential;
					}, 1000)
				};

				if (!credential.hasOwnProperty('acl')) {
					FileService.getFile(file).then(callback);
				} else {
					ShareService.downloadSharedFile(credential, file).then(callback);
				}

			};

		}]);
