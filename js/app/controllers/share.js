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
	.controller('ShareCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService', 'NotificationService', 'SharingACL',
		function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService, NotificationService, SharingACL) {
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
						expire_time: 0,
						expire_views: 0,
						acl: link_acl
					}
				},
				credentialSharedWithUserAndGroup: [],
				cypher_progress: {
					done: 0,
					total: 0
				}
			};


			$scope.accessLevels = [
				{
					label: 'Can edit',
					value: '3'
				},
				{
					label: 'Can view',
					value: '1'
				}
			];
			console.log(SharingACL);
			var acl = new SharingACL(0);

			console.log(acl);
			$scope.inputSharedWith = [];
			$scope.selectedAccessLevel = '1';

			$scope.searchUsers = function ($query) {
				return ShareService.search($query)
			};

			$scope.hasPermission = function(acl, permission){
				return acl.hasPermission(permission);
			};

			$scope.setPermission = function(acl, permission){
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
							acl: angular.copy($scope.default_permissions)
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
				_credential.shared_key = null;
				CredentialService.updateCredential(_credential).then(function () {
					NotificationService.showNotification('Credential unshared', 4000)
				})
			};

			$scope.applyShare = function () {
				$scope.share_settings.cypher_progress.percent = 0;
				$scope.share_settings.cypher_progress.done = 0;
				$scope.share_settings.cypher_progress.total = 0;
				$scope.share_settings.cypher_progress.times = [];
				$scope.share_settings.cypher_progress.times_total = [];

				ShareService.generateSharedKey(20).then(function (key) {
					console.log(key);

					var encryptedSharedCredential = ShareService.encryptSharedCredential($scope.storedCredential, key);
					CredentialService.updateCredential(encryptedSharedCredential, true);

					var list = $scope.share_settings.credentialSharedWithUserAndGroup;
					console.log(list);
					for (var i = 0; i < list.length; i++) {
						var iterator = i; 	// Keeps it available inside the promises callback

						if (list[i].type == "user") {
							ShareService.getVaultsByUser(list[i].userId).then(function (data) {
								$scope.share_settings.cypher_progress.total += data.length;

								list[iterator].vaults = data;
								console.log(data);
								var start = new Date().getTime() / 1000;

								ShareService.cypherRSAStringWithPublicKeyBulkAsync(list[iterator].vaults, key)
									.progress(function (data) {
										$scope.share_settings.cypher_progress.done++;
										$scope.share_settings.cypher_progress.percent = $scope.share_settings.cypher_progress.done / $scope.share_settings.cypher_progress.total * 100;
										$scope.$digest();
									})
									.then(function (result) {
										console.log(result);
										console.log("Took: " + ((new Date().getTime() / 1000) - start) + "s to cypher the string for user [" + data[0].user_id + "]");
										$scope.share_settings.cypher_progress.times.push({
											time: ((new Date().getTime() / 1000) - start),
											user: data[0].user_id
										});
										list[iterator].vaults = result;
										$scope.uploadChanges(list[iterator]);
										$scope.$digest();
									});
							});
						}
					}

					if($scope.share_settings.linkSharing.enabled){
						var shareObj = {
							item_id: $scope.storedCredential.credential_id,
							item_guid: $scope.storedCredential.guid,
							permissions: $scope.share_settings.linkSharing.settings.acl.getAccessLevel(),
							expire_timestamp: $scope.share_settings.linkSharing.settings.expire_time,
							expire_views: $scope.share_settings.linkSharing.settings.expire_views
						};
						ShareService.createPublicSharedCredential(shareObj).then(function(){
							var hash = window.btoa($scope.storedCredential.guid + '<::>'+ key)
							$scope.share_link = $location.$$protocol + '://' + $location.$$host + OC.generateUrl('apps/passman/share/public#') + hash;

						});
					}
				})
			};

			$scope.uploadChanges = function (user) {
				user.accessLevel = angular.copy(user.acl.getAccessLevel());
				ShareService.shareWithUser(storedCredential, user);
				//@TODO Encrypt the credential once all users have the sharing keys.
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
