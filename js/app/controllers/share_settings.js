/**
 * Created by wolfi on 25/09/16.
 */
angular.module('passmanApp')
	.controller('SharingSettingsCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService', 'EncryptService',
										function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService, EncryptService) {
		$scope.vault = VaultService.getActiveVault();
		$scope.sharing_keys = ShareService.getSharingKeys();

        $scope.progress = 1;
        $scope.generating = false;

		$scope.generateKeys = function (length) {
		    $scope.progress = 1;
            $scope.generating = true;

            ShareService.generateRSAKeys(length).progress(function(progress){
                $scope.progress = progress > 0 ? 2:1;
                $scope.$apply();
            }).then(function(kp){
                console.log('stuff done');
                $scope.generating = false;

                var pem = ShareService.rsaKeyPairToPEM(kp)

                $scope.vault.private_sharing_key = EncryptService.encryptString(pem.privateKey);
                $scope.vault.public_sharing_key = pem.publicKey;

                VaultService.updateSharingKeys($scope.vault).then(function (result) {
                    $scope.sharing_keys = ShareService.getSharingKeys();
                })
            });
        }
	}]);