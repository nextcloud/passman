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
		'$rootScope', 'FileService', 'EncryptService', 'TagService', '$timeout', 'NotificationService', 'CacheService', 'ShareService', 'SharingACL',
		function ($scope, VaultService, SettingsService, $location, CredentialService, $rootScope, FileService, EncryptService, TagService, $timeout, NotificationService, CacheService, ShareService, SharingACL) {
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

			var getSharedCredentials = function() {
				ShareService.getCredendialsSharedWithUs($scope.active_vault.guid).then(function (shared_credentials) {
					console.log('Shared credentials', shared_credentials);
					for (var c = 0; c < shared_credentials.length; c++) {
						var _shared_credential = shared_credentials[c];
						var decrypted_key = EncryptService.decryptString(_shared_credential.shared_key);
						console.log(decrypted_key)
						try {
							var _shared_credential_data = ShareService.decryptSharedCredential(_shared_credential.credential_data, decrypted_key);
						} catch (e){

						}
						if(_shared_credential_data){
							delete _shared_credential.credential_data;
							_shared_credential_data.acl = _shared_credential;
							_shared_credential_data.acl.permissions = new SharingACL(_shared_credential_data.acl.permissions);
							_shared_credential_data.tags_raw = _shared_credential_data.tags;
							console.log(_shared_credential_data)
							$scope.active_vault.credentials.push(_shared_credential_data);
						}
					}
				});
			};

			var fetchCredentials = function () {
				VaultService.getVault($scope.active_vault).then(function (vault) {
					var vaultKey = angular.copy($scope.active_vault.vaultKey);
					$scope.active_vault = vault;
					$scope.active_vault.vaultKey = vaultKey;
					VaultService.setActiveVault($scope.active_vault);
					var _credentials = [];
					for (var i = 0; i < $scope.active_vault.credentials.length; i++) {
						try {
							if(!$scope.active_vault.credentials[i].shared_key) {
								$scope.active_vault.credentials[i] = CredentialService.decryptCredential(angular.copy(vault.credentials[i]));

							} else {
								var enc_key = EncryptService.decryptString(vault.credentials[i].shared_key);
								console.log(enc_key)
								$scope.active_vault.credentials[i] = ShareService.decryptSharedCredential(angular.copy(vault.credentials[i]), enc_key);
							}
							$scope.active_vault.credentials[i].tags_raw = $scope.active_vault.credentials[i].tags;
						} catch (e) {
							NotificationService.showNotification('An error happend during decryption', 5000);
							$rootScope.$broadcast('logout');
							SettingsService.setSetting('defaultVaultPass', null);
							SettingsService.setSetting('defaultVault', null);
							$location.path('/')

						}
						if ($scope.active_vault.credentials[i].tags) {
							TagService.addTags($scope.active_vault.credentials[i].tags);
						}
					}
					getSharedCredentials();
					$scope.show_spinner = false;
				});
			};

			if ($scope.active_vault) {
				$scope.$parent.selectedVault = true;
				fetchCredentials()
			}

			ShareService.getPendingRequests().then(function (shareRequests) {
				if(shareRequests.length > 0){
					$scope.incoming_share_requests = shareRequests;
					jQuery('.share_popup').dialog({
						width: 600,
						position:['center', 90]
					});
				}
			});

			$scope.permissions = new SharingACL(0);

			$scope.hasPermission = function(acl, permission){
				if(acl) {
					return acl.hasPermission(permission);
				} else {
					return true;
				}

			};
			
			$scope.acceptShareRequest = function(share_request){
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
					for(var v = 0; v < $scope.incoming_share_requests.length; v++){
						if($scope.incoming_share_requests[v].target_vault_id == $scope.active_vault.vault_id){
							active_share_requests = true;
						}
					}
					if(active_share_requests === false){
						jQuery('.ui-dialog').remove();
						getSharedCredentials();
					}
					console.log(result)
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
				$location.path('/vault/' + $scope.active_vault.vault_id + '/edit/' + _credential.credential_id)
			};

			$scope.getRevisions = function (credential) {
				var _credential = angular.copy(credential);
				$rootScope.$emit('app_menu', false);
				SettingsService.setSetting('revision_credential', CredentialService.encryptCredential(_credential));
				$location.path('/vault/' + $scope.active_vault.vault_id + '/' + _credential.credential_id + '/revisions')
			};

			$scope.shareCredential = function (credential) {
				var _credential = angular.copy(credential);
				$rootScope.$emit('app_menu', false);
				SettingsService.setSetting('share_credential', CredentialService.encryptCredential(_credential));
				$location.path('/vault/' + $scope.active_vault.vault_id + '/' + _credential.credential_id + '/share')
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


			$scope.downloadFile = function (file) {
				FileService.getFile(file).then(function (result) {
					var file_data = EncryptService.decryptString(result.file_data);
					var uriContent = FileService.dataURItoBlob(file_data, file.mimetype), a = document.createElement("a");
					a.style = "display: none";
					a.href = uriContent;
					a.download = escapeHTML(file.filename);
					document.body.appendChild(a);
					a.click();
					window.URL.revokeObjectURL(uriContent);
				});
			};

		}]);
