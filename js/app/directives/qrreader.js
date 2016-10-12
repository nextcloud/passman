(function () {
	'use strict';

	/**
	 * @ngdoc directive
	 * @name passmanApp.directive:passwordGen
	 * @description
	 * # passwordGen
	 */
	angular.module('passmanApp').directive("qrread", ['$parse',
		function ($parse) {
			return {
				scope: true,
				link: function (scope, element, attributes) {
					var invoker = $parse(attributes.onRead);
					scope.imageData = null;

					qrcode.callback = function (result) {
						//console.log('QR callback:',result);
						invoker(scope, {
							qrdata: {
								qrData: result,
								image: scope.imageData
							}
						});
						//element.val('');
					};
					element.bind("change", function (changeEvent) {
						var reader = new FileReader(), file = changeEvent.target.files[0];
						reader.readAsDataURL(file);
						reader.onload = (function () {
							return function (e) {
								//gCtx.clearRect(0, 0, gCanvas.width, gCanvas.height);
								scope.imageData = e.target.result;
								qrcode.decode(e.target.result);
							};
						})(file);
					});
				}
			};
		}
	]);
}());