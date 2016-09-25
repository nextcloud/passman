'use strict';

/**
 * @ngdoc service
 * @name passmanApp.ShareService
 * @description
 * # ShareService
 * Service in the passmanApp.
 */
angular.module('passmanApp')
	.service('ShareService', ['$http', function ($http) {
		var _tags = [];
		return {
			search: function (string) {
				var queryUrl = OC.generateUrl('apps/passman/api/v2/sharing/search');
				return $http.post(queryUrl, {search: string}).then(function (response) {
					if (response.data) {
						return response.data;
					} else {
						return response;
					}
				});
			},
			generateRSAKeys: function(key_length, progress, callback){
				// return forge.pki.rsa.generateKeyPair(key_length);

				var state = forge.pki.rsa.createKeyPairGenerationState(key_length, 0x10001);
				var step = function() {
					// run for 100 ms
					if(!forge.pki.rsa.stepKeyPairGenerationState(state, 100)) {
						console.log(state);
						console.log({
							// 'data_length': state.n.data.length,
							'bits' : state.bits,
							'pBits': state.pBits,
							'qBits': state.qBits
						});
						setTimeout(step, 1);
					}
					else {
						// done, turn off progress indicator, use state.keys
						callback(state.keys);
					}
				};
				// turn on progress indicator, schedule generation to run
				setTimeout(step);
			},
			rsaKeyPairToPEM: function(keypair){
				return {
					'publicKey' 	: forge.pki.publicKeyToPem(keypair.publicKey),
					'privateKey' 	: forge.pki.privateKeyToPem(keypair.privateKey)
				};
			},
			rsaPrivateKeyFromPEM: function(private_pem) {
				return forge.pki.privateKeyFromPem(private_pem);
			},
			rsaPublicKeyFromPEM: function(public_pem){
				return forge.pki.publicKeyFromPem(public_pem);
			}
		}
	}]);
