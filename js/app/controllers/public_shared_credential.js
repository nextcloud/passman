/**
 * @ngdoc function
 * @name passmanApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the passmanApp
 */
angular.module('passmanApp')
	.controller('PublicSharedCredential', ['$scope', 'ShareService','$window', function ($scope, ShareService, $window) {
		$scope.test = 'hello world';

		$scope.loading = false;
		$scope.loadSharedCredential = function () {
			$scope.loading = true;
			var data = window.atob($window.location.hash.replace('#','')).split('<::>');
			var guid = data[0];
			var _key = data[1];
			ShareService.getPublicSharedCredential(guid).then(function (sharedCredential) {
				$scope.loading = false;
				if(sharedCredential.status === 200){
					var _credential = ShareService.decryptSharedCredential(sharedCredential.data.credential_data, _key);
					$scope.shared_credential = _credential;
				} else {
					$scope.expired = true;
				}

			})

		}
	}])
;

