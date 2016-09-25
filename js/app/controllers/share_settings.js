/**
 * Created by wolfi on 25/09/16.
 */
angular.module('passmanApp')
	.controller('SharingSettingsCtrl', ['$scope', 'VaultService', 'CredentialService', 'SettingsService', '$location', '$routeParams', 'ShareService', 'EncryptService',
										function ($scope, VaultService, CredentialService, SettingsService, $location, $routeParams, ShareService, EncryptService) {
		$scope.active_vault = VaultService.getActiveVault();
        $scope.progress = 1;
        $scope.generating = false;

		$scope.sharing_keys = ShareService.getSharingKeys();

		$scope.generateKeys = function (length) {
		    $scope.progress = 1;
            $scope.generating = true;

            ShareService.generateRSAKeys(length, function(progress){
                $scope.progress = progress > 0 ? 2:1;
                $scope.$apply();
                console.log($scope.progress);
            }, function(kp){
                $scope.generating = false;

                var pem = ShareService.rsaKeyPairToPEM(kp)

                $scope.active_vault.private_sharing_key = pem.privateKey;
                $scope.active_vault.public_sharing_key = pem.publicKey;


				var _vault = angular.copy($scope.active_vault);
				_vault.private_sharing_key = EncryptService.encryptString(_vault.private_sharing_key);
                VaultService.updateSharingKeys(_vault).then(function (result) {
                    console.log('done')
                })
            });
        }
	}]);