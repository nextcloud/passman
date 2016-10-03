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


		var example_shared_credential = {
			"credential_id": 292,
			"guid": "3D18EAD3-CF40-4B2B-B568-82CD7CB3D47F",
			"user_id": "sander",
			"vault_id": 2,
			"label": "donnelly.com",
			"description": null,
			"created": 1475479693,
			"changed": 1475479693,
			"tags": [{"text": "Games"}],
			"email": null,
			"username": "ebrekke",
			"password": "hd%/U_%vzvh%",
			"url": "http://api.namefake.com/english-united-states/male/2854dda4938c9c5f60a288fa6fbe5095",
			"favicon": null,
			"renew_interval": null,
			"expire_time": 0,
			"delete_time": 0,
			"files": [{
				"file_id": 1,
				"filename": "20160925-Clipperz_Export.html",
				"guid": "6DA2CE41-A26B-4F97-A334-2CC74F7E9890",
				"size": 13863,
				"created": 1475485368,
				"mimetype": "text/html",
				"$$hashKey": "object:1261"
			}, {
				"file_id": 2,
				"filename": "20160925_Clipperz_Offline.html",
				"guid": "9337D189-B79E-4750-BEF9-3C912A9EA59D",
				"size": 3088428,
				"created": 1475485376,
				"mimetype": "text/html",
				"$$hashKey": "object:1268"
			}],
			"custom_fields": [{
				"label": "Test field",
				"value": "blah blah",
				"secret": false,
				"$$hashKey": "object:1205"
			}, {
				"label": "another field =)",
				"value": "vlaue",
				"secret": true,
				"$$hashKey": "object:1220"
			}],
			"otp": {
				"type": "totp",
				"label": "Google:fake@gmail.com",
				"qr_uri": {
					"qrData": "otpauth://totp/Google%3Afake%40gmail.com?secret=oyonyttithtryvpnqqrxluytgwon2mhw&issuer=Google",
					"image": ""
				},
				"secret": "oyonyttithtryvpnqqrxluytgwon2mhw",
				"issuer": "Google"
			},
			"hidden": 0,
			"shared_key": null,
			"tags_raw": [{"text": "Games"}]
		};

		$scope.loadSharedCredential = function () {
			$scope.loading = true;
			var guid = $window.location.hash.replace('#','');
			ShareService.getPublicSharedCredential(guid).then(function (sharedCredential) {
				$scope.loading = false;
				console.log(sharedCredential)
				if(sharedCredential.status === 200){
					$scope.shared_credential = example_shared_credential;
				} else {
					$scope.expired = true;
				}

			}, function(error){
				return false;
			})

		}
	}])
;

