'use strict';
angular
	.module('passmanApp', [
		'ngAnimate',
		'ngCookies',
		'ngResource',
		'ngRoute',
		'ngSanitize',
		'ngTouch',
		'ngclipboard',

	]).config(['$httpProvider', function ($httpProvider) {
	$httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;
}]);
