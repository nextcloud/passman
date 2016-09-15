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
		'ngPasswordMeter',
		'ngclipboard',
		'xeditable',
		'ngTagsInput'
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
			.when('/vault/:vault_id/new', {
				templateUrl: 'views/edit_credential.html',
				controller: 'CredentialEditCtrl'
			})
			.when('/vault/:vault_id/edit/:credential_id', {
				templateUrl: 'views/edit_credential.html',
				controller: 'CredentialEditCtrl'
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
