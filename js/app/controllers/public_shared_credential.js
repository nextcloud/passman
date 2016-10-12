(function () {
	'use strict';

	/**
	 * @ngdoc function
	 * @name passmanApp.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the passmanApp
	 */
	angular.module('passmanApp')
		.controller('PublicSharedCredential', ['$scope', 'ShareService', '$window', 'EncryptService', 'NotificationService', function ($scope, ShareService, $window, EncryptService, NotificationService) {
			var _key;
			$scope.loading = false;
			$scope.loadSharedCredential = function () {
				$scope.loading = true;
				var data = window.atob($window.location.hash.replace('#', '')).split('<::>');
				var guid = data[0];
				_key = data[1];
				ShareService.getPublicSharedCredential(guid).then(function (sharedCredential) {
					$scope.loading = false;
					if (sharedCredential.status === 200) {
						$scope.shared_credential = ShareService.decryptSharedCredential(sharedCredential.data.credential_data, _key);
					} else {
						$scope.expired = true;
					}

				});

			};

			$scope.downloadFile = function (credential, file) {
				ShareService.downloadSharedFile(credential, file).then(function (result) {
					var key = null;
					if (!result.hasOwnProperty('file_data')) {
						NotificationService.showNotification('Error downloading file, you probably don\'t have enough permissions', 5000);
						return;
					}
					var file_data = EncryptService.decryptString(result.file_data, _key);
					download(file_data, escapeHTML(file.filename), file.mimetype);
				});
			};
		}]);
}());
