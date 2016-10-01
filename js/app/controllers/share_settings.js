/**
 * Created by wolfi on 25/09/16.
 */
angular.module('passmanApp')
	.controller('SharingSettingsCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService', 'EncryptService',
										function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService, EncryptService) {
		$scope.active_vault = VaultService.getActiveVault();
		$scope.sharing_keys = ShareService.getSharingKeys();

        $scope.progress = 1;
        $scope.generating = false;



        $scope.available_sizes = [
            {
                size: 1024,
                name: 1024
            },
            {
                size: 2048,
                name: 2048
            },
            {
                size: 4096,
                name: 4096
            }
        ];

        $scope.setKeySize = function (size) {
            for (var i = 0; i < $scope.available_sizes.length; i++) {
                if ($scope.available_sizes[i].size == size) {
                    $scope.key_size = $scope.available_sizes[i];
                    return;
                }
            }
        };

        $scope.setKeySize(2048);

		$scope.generateKeys = function (length) {
		    $scope.progress = 1;
            $scope.generating = true;

            ShareService.generateRSAKeys(length).progress(function(progress){
                $scope.progress = progress > 0 ? 2:1;
                $scope.$digest();
            }).then(function(kp){
                console.log('stuff done');
                $scope.generating = false;

                var pem = ShareService.rsaKeyPairToPEM(kp)

                $scope.active_vault.private_sharing_key = EncryptService.encryptString(pem.privateKey);
                $scope.active_vault.public_sharing_key = pem.publicKey;

                VaultService.updateSharingKeys($scope.active_vault).then(function (result) {
                    $scope.sharing_keys = ShareService.getSharingKeys();
                })
            });
        }
	}]);