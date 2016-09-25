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
						// console.log(state);
						if (state.p !== null) {
							progress(50);
						}
						else {
							progress(0);
						}

						// console.log({
						// 	// 'data_length': state.n.data.length,
						// 	'bits' : state.bits,
						// 	'num' : state.num,
						// 	'numBitLength' : state.num !== null ? state.num.bitLength() : null,
						// 	'pBitLength' : state.p !== null ? state.p.bitLength() : null,
						// 	'qBitLength' : state.q !== null ? state.q.bitLength() : null,
						// 	'pqState' : state.pqState,
						// 	'pBits': state.pBits,
						// 	'qBits': state.qBits
						// });
						setTimeout(step, 1);
					}
					else {
						callback(state.keys);
					}
				};
				setTimeout(step, 100);
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
