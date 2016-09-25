/**
 * Created by wolfi on 25/09/16.
 */
angular.module('passmanApp')
	.controller('SharingSettingsCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService',
										function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService) {
		$scope.active_vault = VaultService.getActiveVault();

		$scope.generateKeys = function (length) {
			var rsa = ShareService.rsaKeyPairToPEM(ShareService.generateRSAKeys(length));
			console.log(rsa);
			$scope.active_vault.private_sharing_key = rsa.privateKey;
			$scope.active_vault.public_sharing_key = rsa.publicKey;
			VaultService.updateSharingKeys($scope.active_vault).then(function (result) {
				console.log('done')
			})

		}
	}]);