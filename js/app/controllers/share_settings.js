/**
 * Created by wolfi on 25/09/16.
 */
angular.module('passmanApp')
	.controller('SharingSettingsCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService',
										function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService) {
		$scope.active_vault = VaultService.getActiveVault();

		$scope.generateKeys = function (length) {
        // var rsa = ShareService.rsaKeyPairToPEM(ShareService.generateRSAKeys(length));
        ShareService.generateRSAKeys(length, function(progress){
            console.log(progress);
        }, function(kp){
           console.log(kp);
           var pem = ShareService.rsaKeyPairToPEM(kp)
           $scope.active_vault.private_sharing_key = pem.privateKey;
           $scope.active_vault.public_sharing_key = pem.publicKey;
           VaultService.updateSharingKeys($scope.active_vault).then(function (result) {
                console.log('done')
           })
        });
        // console.log(rsa);
        // $scope.active_vault.private_sharing_key = rsa.privateKey;
        // $scope.active_vault.public_sharing_key = rsa.publicKey;
        // console.log(ShareService.rsaPublicKeyFromPEM(rsa.publicKey));
        // console.log(ShareService.rsaPrivateKeyFromPEM(rsa.privateKey));
		}
	}]);