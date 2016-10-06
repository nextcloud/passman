'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 * This file is part of passman, licensed under AGPLv3
 */
angular.module('passmanApp')
	.controller('ShareCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService', 'NotificationService', 'SharingACL', 'EncryptService', 'FileService',
		function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService, NotificationService, SharingACL, EncryptService, FileService) {
			$scope.active_vault = VaultService.getActiveVault();

			$scope.tabs = [{
				title: 'Share with users and groups',
				url: 'views/partials/forms/share_credential/basics.html',
			}, {
				title: 'Share link',
				url: 'views/partials/forms/share_credential/link_sharing.html',
				color: 'green'
			}];
			$scope.currentTab = {
				title: 'General',
				url: 'views/partials/forms/share_credential/basics.html'
			};

			$scope.onClickTab = function (tab) {
				$scope.currentTab = tab;
			};

			$scope.isActiveTab = function (tab) {
				return tab.url == $scope.currentTab.url;
			};

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

				}
			}
			var storedCredential = SettingsService.getSetting('share_credential');

			if (!storedCredential) {
				$location.path('/vault/' + $routeParams.vault_id);
			} else {
				$scope.storedCredential = CredentialService.decryptCredential(angular.copy(storedCredential));
			}

			if ($scope.active_vault) {
				$scope.$parent.selectedVault = true;
			}
			$scope.cancel = function () {
				SettingsService.setSetting('share_credential', null);
				$location.path('/vault/' + $scope.storedCredential.vault_id);
			};


			$scope.default_permissions = new SharingACL(0);
			$scope.default_permissions.addPermission(
				$scope.default_permissions.permissions.READ |
				$scope.default_permissions.permissions.WRITE |
				$scope.default_permissions.permissions.FILES
			);

			var link_acl = angular.copy($scope.default_permissions);
			link_acl.removePermission($scope.default_permissions.permissions.WRITE)

			$scope.share_settings = {
				linkSharing: {
					enabled: false,
					settings: {
						expire_time: new Date("2999-12-31T22:59:59"),
						expire_views: 5,
						acl: link_acl
					}
				},
				credentialSharedWithUserAndGroup: [],
				cypher_progress: {
					done: 0,
					total: 0
				},
				upload_progress: {
					done: 0,
					total: 0
				}
			};

			var getAcl = function () {
				ShareService.getSharedCredentialACL($scope.storedCredential).then(function (aclList) {
					var _list = []
					var enc_key = ($scope.storedCredential.shared_key) ? EncryptService.decryptString(angular.copy($scope.storedCredential.shared_key)) : false;
					for (var i = 0; i < aclList.length; i++) {
						var acl = aclList[i];
						if (acl.user_id === null) {
							$scope.share_settings.linkSharing = {
								enabled: true,
								settings: {
									expire_time: new Date(acl.expire * 1000),
									expire_views: acl.expire_views,
									acl: new SharingACL(acl.permissions)
								}
							};
							if (enc_key) {
								var hash = window.btoa($scope.storedCredential.guid + '<::>' + enc_key)
								$scope.share_link = $location.$$protocol + '://' + $location.$$host + OC.generateUrl('apps/passman/share/public#') + hash;
							}
						} else {
							var obj = {
								userId: acl.user_id,
								displayName: acl.user_id,
								type: 'user',
								acl: new SharingACL(acl.permissions),
								acl_id: acl.acl_id,
								pending: acl.pending,
								credential_guid: acl.item_guid,
								created: acl.created
							};

							_list.push(obj);
						}

					}
					$scope.share_settings.credentialSharedWithUserAndGroup = _list;
				});
			};
			getAcl();
			var acl = new SharingACL(0);


			$scope.$watch('share_settings.upload_progress.done', function () {
				console.log();
				if ($scope.share_settings.upload_progress.done == $scope.share_settings.upload_progress.total) {
					getAcl()
				}
			});

			$scope.inputSharedWith = [];
			$scope.selectedAccessLevel = '1';

			$scope.searchUsers = function ($query) {
				return ShareService.search($query)
			};

			$scope.hasPermission = function (acl, permission) {
				return acl.hasPermission(permission);
			};

			$scope.setPermission = function (acl, permission) {
				acl.togglePermission(permission);
			};
			$scope.shareWith = function (shareWith, selectedAccessLevel) {
				//@TODO Improve this so we can add, edit and remove users and permissions.
				$scope.inputSharedWith = [];
				if (shareWith.length > 0) {
					for (var i = 0; i < shareWith.length; i++) {
						var obj = {
							userId: shareWith[i].uid,
							displayName: shareWith[i].text,
							type: shareWith[i].type,
							acl: angular.copy($scope.default_permissions),
							pending: true,
							credential_guid: $scope.storedCredential.guid
						};
						if ($scope.share_settings.credentialSharedWithUserAndGroup.indexOf(obj) === -1) {
							$scope.share_settings.credentialSharedWithUserAndGroup.push(obj)
						}
					}
				}
			};

			$scope.unshareCredential = function (credential) {
				ShareService.unshareCredential(credential);
				var _credential = angular.copy(credential);
				var enc_key = EncryptService.decryptString(angular.copy(_credential.shared_key));
				_credential.shared_key = null;
				_credential.unshare_action = true;
				CredentialService.updateCredential(_credential).then(function () {
					NotificationService.showNotification('Credential unshared', 4000)
				});

				for (var f = 0; f < $scope.storedCredential.files.length; f++) {
					var _file = $scope.storedCredential.files[f];
					FileService.getFile(_file).then(function (fileData) {
						//Decrypt with old key
						fileData.filename = EncryptService.decryptString(fileData.filename, enc_key);
						fileData.file_data = EncryptService.decryptString(fileData.file_data, enc_key);
						FileService.updateFile(fileData, $scope.active_vault.vaultKey);
					})
				}

				CredentialService.getRevisions($scope.storedCredential.guid).then(function (revisions) {
					for (var r = 0; r < revisions.length; r++) {
						var _revision = revisions[r];
						_revision.credential_data = ShareService.decryptSharedCredential(_revision.credential_data, enc_key);
						_revision.credential_data = CredentialService.encryptCredential(_revision.credential_data);
						console.log('Used key for encrypting history ', enc_key);
						CredentialService.updateRevision(_revision);
					}
				});
			};

			/**
			 * Apply a share to a new user
			 * @param user A user object to who we should share the data
			 * @param enc_key The shared key we are going to ecnrypt with his public rsa key
			 */
			$scope.applyShareToUser = function (user, enc_key) {
				ShareService.getVaultsByUser(user.userId).then(function (data) {
					$scope.share_settings.cypher_progress.total += data.length;

					user.vaults = data;
					var start = new Date().getTime() / 1000;
					ShareService.cypherRSAStringWithPublicKeyBulkAsync(user.vaults, enc_key)
						.progress(function (data) {
							$scope.share_settings.cypher_progress.done++;
							$scope.share_settings.cypher_progress.percent = $scope.share_settings.cypher_progress.done / $scope.share_settings.cypher_progress.total * 100;
							$scope.$digest();
						})
						.then(function (result) {
							console.log("Took: " + ((new Date().getTime() / 1000) - start) + "s to cypher the string for user [" + data[0].user_id + "]");
							$scope.share_settings.cypher_progress.times.push({
								time: ((new Date().getTime() / 1000) - start),
								user: data[0].user_id
							});
							user.vaults = result;
							if (!user.hasOwnProperty('acl_id')) {
								$scope.uploadChanges(user);
							}
							$scope.$digest();
						});
				});
			};
			$scope.sharing_complete = true;
			$scope.applyShare = function () {
				$scope.sharing_complete = false;
				$scope.share_settings.cypher_progress.percent = 0;
				$scope.share_settings.cypher_progress.done = 0;
				$scope.share_settings.cypher_progress.total = 0;
				$scope.share_settings.cypher_progress.times = [];
				$scope.share_settings.cypher_progress.times_total = [];
				$scope.share_settings.upload_progress.done = 0;
				$scope.share_settings.upload_progress.total = 0;
				//Credential is already shared
				if ($scope.storedCredential.shared_key && $scope.storedCredential.shared_key != '' && $scope.storedCredential.shared_key != null) {
					console.log('Shared key found');
					var enc_key = EncryptService.decryptString(angular.copy($scope.storedCredential.shared_key));
					if ($scope.share_settings.linkSharing.enabled) {
						var expire_time = new Date(angular.copy($scope.share_settings.linkSharing.settings.expire_time)).getTime() / 1000;
						var shareObj = {
							item_id: $scope.storedCredential.credential_id,
							item_guid: $scope.storedCredential.guid,
							permissions: $scope.share_settings.linkSharing.settings.acl.getAccessLevel(),
							expire_timestamp: expire_time,
							expire_views: $scope.share_settings.linkSharing.settings.expire_views
						};
						ShareService.createPublicSharedCredential(shareObj).then(function () {
							var hash = window.btoa($scope.storedCredential.guid + '<::>' + enc_key)
							$scope.share_link = $location.$$protocol + '://' + $location.$$host + OC.generateUrl('apps/passman/share/public#') + hash;
						})
					}

					var list = $scope.share_settings.credentialSharedWithUserAndGroup;

					for (var i = 0; i < list.length; i++) {
						var iterator = i;
						var target_user = list[i];
						if (target_user.hasOwnProperty('created')) {
							console.log('Updating permissions')

							var acl = {
								user_id: target_user.userId,
								permission: target_user.acl.getAccessLevel()
							};
							ShareService.updateCredentialAcl($scope.storedCredential, acl);
						} else {
							console.log('Creating new share')
							$scope.applyShareToUser(list[iterator], enc_key);
						}
					}

				} else {

					ShareService.generateSharedKey(20).then(function (key) {
						var encryptedSharedCredential = ShareService.encryptSharedCredential($scope.storedCredential, key);
						// encryptedSharedCredential.set_share_key = true;
						// CredentialService.updateCredential(encryptedSharedCredential, true).then(function (sharedCredential) {
						// 	$scope.storedCredential = ShareService.decryptSharedCredential(sharedCredential, key);
						// });
                        //
						// //@TODO Update files with new key (async)
						// // Files are stored in $scope.storedCredential.files
						// // They need get downloaded with FileService.getFile
						// // Then decrypt the data obtained with var EncryptService.decryptString(result.file_data);
						// // To update a file you can use the FileService.updateFile
                        //
						// for (var f = 0; f < $scope.storedCredential.files.length; f++) {
						// 	var _file = $scope.storedCredential.files[f];
						// 	FileService.getFile(_file).then(function (fileData) {
						// 		//Decrypt with old key
						// 		fileData.filename = EncryptService.decryptString(fileData.filename);
						// 		fileData.file_data = EncryptService.decryptString(fileData.file_data);
						// 		FileService.updateFile(fileData, key);
						// 	})
						// }
                        //
						// CredentialService.getRevisions($scope.storedCredential.guid).then(function (revisions) {
						// 	for (var r = 0; r < revisions.length; r++) {
						// 		var _revision = revisions[r];
						// 		//Decrypt!
						// 		_revision.credential_data = CredentialService.decryptCredential(_revision.credential_data);
						// 		_revision.credential_data = ShareService.encryptSharedCredential(_revision.credential_data, key);
						// 		console.log('Used key for encrypting history ', key);
						// 		CredentialService.updateRevision(_revision);
						// 	}
						// });
						var old_key = VaultService.getActiveVault().vaultKey
						console.log(encryptedSharedCredential);
						CredentialService.reencryptCredential(encryptedSharedCredential.credential_id, old_key, key).progress(function(data){
							console.log(data);
						}).then(function(data){
							console.log(data);

						});

						//@TODO Update revisions with new key (async)
						// With CredentialService.getRevisions we can get the revisions.
						// Then we can update them using CredentialService.updateRevision

						var list = $scope.share_settings.credentialSharedWithUserAndGroup;
						for (var i = 0; i < list.length; i++) {
							if (list[i].type == "user") {
								$scope.applyShareToUser(list[i], key);
							}
						}

						if ($scope.share_settings.linkSharing.enabled) {
							var expire_time = new Date(angular.copy($scope.share_settings.linkSharing.settings.expire_time)).getTime() / 1000;
							var shareObj = {
								item_id: $scope.storedCredential.credential_id,
								item_guid: $scope.storedCredential.guid,
								permissions: $scope.share_settings.linkSharing.settings.acl.getAccessLevel(),
								expire_timestamp: expire_time,
								expire_views: $scope.share_settings.linkSharing.settings.expire_views
							};
							ShareService.createPublicSharedCredential(shareObj).then(function () {
								var hash = window.btoa($scope.storedCredential.guid + '<::>' + key);
								$scope.share_link = $location.$$protocol + '://' + $location.$$host + OC.generateUrl('apps/passman/share/public#') + hash;

							});
						}
						NotificationService.showNotification('Credential shared', 4000)
					})
				}
			};

			$scope.uploadChanges = function (user) {
				$scope.share_settings.upload_progress.total++;

				user.accessLevel = angular.copy(user.acl.getAccessLevel());
				ShareService.shareWithUser(storedCredential, user)
					.then(function (data) {
						$scope.share_settings.upload_progress.done++;
						$scope.share_settings.upload_progress.percent = $scope.share_settings.upload_progress.done / $scope.share_settings.upload_progress.total * 100;
					});
			};

			$scope.calculate_total_time = function () {
				$scope.share_settings.cypher_progress.times = $scope.share_settings.cypher_progress.times || [];
				var total = 0;
				for (var i = 0; i < $scope.share_settings.cypher_progress.times.length; i++) {
					total += $scope.share_settings.cypher_progress.times[i].time;
				}
				return total;
			}
		}]);
