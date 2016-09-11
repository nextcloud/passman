'use strict';

/**
 * @ngdoc overview
 * @name passmanApp
 * @description
 * # passmanApp
 *
 * Main module of the application.
 */
angular
	.module('passmanApp', [
		'ngAnimate',
		'ngCookies',
		'ngResource',
		'ngRoute',
		'ngSanitize',
		'ngTouch',
		'templates-main',
		'LocalStorageModule',
		'offClick',
		'ngPasswordMeter'
	])
	.config(function ($routeProvider) {
		$routeProvider
			.when('/', {
				templateUrl: 'views/vaults.html',
				controller: 'VaultCtrl'
			})
			.when('/vault/:vault_id', {
				templateUrl: 'views/show_vault.html',
				controller: 'CredentialCtrl'
			})
			.otherwise({
				redirectTo: '/'
			});
	}).config(['$httpProvider', function ($httpProvider) {
	$httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;
}]).config(function (localStorageServiceProvider) {
	localStorageServiceProvider
		.setNotify(true, true);
});
