'use strict';

/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('ShareCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService', function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService) {
		$scope.active_vault = VaultService.getActiveVault();

		$scope.tabs = [{
			title: 'Share with users and groups',
			url: 'views/partials/forms/share_credential/basics.html',
		}, {
			title: 'Share link',
			url: 'views/partials/forms/share_credential/expire_settings.html',
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
		$scope.cancel = function(){
			SettingsService.setSetting('share_credential', null);
			$location.path('/vault/' + $scope.storedCredential.vault_id);
		};



		$scope.share_settings = {
			credentialSharedWithUserAndGroup:[]
		};

		$scope.accessLevels = [
			{
				label: 'Can edit',
				value: '2'
			},
			{
				label: 'Can view',
				value: '1'
			}
		];

		$scope.inputSharedWith = [];
		$scope.selectedAccessLevel = '1';

		$scope.searchUsers = function($query){
			 return ShareService.search($query)
		};

		$scope.shareWith = function(shareWith, selectedAccessLevel){
			$scope.inputSharedWith = [];
			if(shareWith.length > 0) {
				for (var i = 0; i < shareWith.length; i++) {
					$scope.share_settings.credentialSharedWithUserAndGroup.push(
						{
							userId: shareWith[i].uid,
							displayName: shareWith[i].text,
							type: shareWith[i].type,
							accessLevel: selectedAccessLevel
						}
					)
				}
			}
		}

		$scope.applyShare = function(){
			console.log("boom!");
			var list = $scope.share_settings.credentialSharedWithUserAndGroup;
			console.log(list);
			for (var i = 0; i < list.length; i++){
				ShareService.getVaultsByUser(list[i].user_id).then(function(data){
					console.log(data);
				})
			}
		}
	}]);
