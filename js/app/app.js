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
			}).when('/vault/:vault_id/:credential_id/share', {
				templateUrl: 'views/share_credential.html',
				controller: 'ShareCtrl'
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

/**
 * jQuery for notification handling D:
 **/
jQuery(document).ready(function () {
	var findItemByID = function(id){
		var credentials,foundItem=false;
		credentials = angular.element('#app-content-wrapper').scope().credentials;
		console.log(id, credentials)
		angular.forEach(credentials, function(credential){
			if(credential.credential_id == id){
				foundItem = credential;
			}
		});
		return foundItem;
	};
	jQuery(document).on('click', '.undoDelete', function () {
		var credential = findItemByID($(this).attr('data-item-id'));
		angular.element('#app-content-wrapper').scope().recoverCredential(credential);
		angular.element('#app-content-wrapper').scope().$apply();
	});
	jQuery(document).on('click', '.undoRestore', function () {
		var credential = findItemByID($(this).attr('data-item-id'));
		angular.element('#app-content-wrapper').scope().deleteCredential(credential);
		angular.element('#app-content-wrapper').scope().$apply();
	});
	var adjustControlsWidth = function() {
		if($('#controls').length) {
			var controlsWidth;
			// if there is a scrollbar …
			if($('#app-content').get(0).scrollHeight > $('#app-content').height()) {
				if($(window).width() > 768) {
					controlsWidth = $('#content').width() - $('#app-navigation').width() - OC.Util.getScrollBarWidth();
					if (!$('#app-sidebar').hasClass('hidden') && !$('#app-sidebar').hasClass('disappear')) {
						controlsWidth -= $('#app-sidebar').width();
					}
				} else {
					controlsWidth = $('#content').width() - OC.Util.getScrollBarWidth();
				}
			} else { // if there is none
				if($(window).width() > 768) {
					controlsWidth = $('#content').width() - $('#app-navigation').width();
					if (!$('#app-sidebar').hasClass('hidden') && !$('#app-sidebar').hasClass('disappear')) {
						//controlsWidth -= $('#app-sidebar').width();
					}
				} else {
					controlsWidth = $('#content').width();
				}
			}
			$('#controls').css('width', controlsWidth);
			$('#controls').css('min-width', controlsWidth);
		}
	};
	$(window).resize(_.debounce(adjustControlsWidth, 256));
	adjustControlsWidth()
});