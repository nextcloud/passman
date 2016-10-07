(function () {
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
			'ngTagsInput',
			'angularjs-datetime-picker'
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
			}).when('/vault/:vault_id/:credential_id/revisions', {
				templateUrl: 'views/credential_revisions.html',
				controller: 'RevisionCtrl'
			})
				.when('/vault/:vault_id/settings', {
					templateUrl: 'views/settings.html',
					controller: 'SettingsCtrl'
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
		var findItemByID = function (id) {
			var credentials, foundItem = false;
			credentials = angular.element('#app-content-wrapper').scope().credentials;
			angular.forEach(credentials, function (credential) {
				if (credential.credential_id === id) {
					foundItem = credential;
				}
			});
			return foundItem;
		};
		jQuery(document).on('click', '.undoDelete', function () {
			var credential = findItemByID($(this).attr('data-item-id'));
			angular.element('#app-content-wrapper').scope().recoverCredential(credential);
			//Outside anglular we need $apply
			angular.element('#app-content-wrapper').scope().$apply();
		});
		jQuery(document).on('click', '.undoRestore', function () {
			var credential = findItemByID($(this).attr('data-item-id'));
			angular.element('#app-content-wrapper').scope().deleteCredential(credential);
			//Outside anglular we need $apply
			angular.element('#app-content-wrapper').scope().$apply();
		});
		var adjustControlsWidth = function (r) {
			if ($('#controls').length) {
				var controlsWidth;
				// if there is a scrollbar …
				if ($('#app-content').get(0)) {
					if ($('#app-content').get(0).scrollHeight > $('#app-content').height()) {
						if ($(window).width() > 768) {
							controlsWidth = $('#content').width() - $('#app-navigation').width() - OC.Util.getScrollBarWidth();
							if (!$('#app-sidebar').hasClass('hidden') && !$('#app-sidebar').hasClass('disappear')) {
								controlsWidth -= $('#app-sidebar').width();
							}
						} else {
							controlsWidth = $('#content').width() - OC.Util.getScrollBarWidth();
						}
					} else { // if there is none
						if ($(window).width() > 768) {
							controlsWidth = $('#content').width() - $('#app-navigation').width();
							if (!$('#app-sidebar').hasClass('hidden') && !$('#app-sidebar').hasClass('disappear')) {
								//controlsWidth -= $('#app-sidebar').width();
							}
						} else {
							controlsWidth = $('#content').width();
						}
					}
				}
				var magic;
				if (r) {
					magic = 0;
				} else {
					magic = 85;
				}
				$('#controls').css('width', controlsWidth + magic);
				$('#controls').css('min-width', controlsWidth + magic);
			}
		};
		$(window).resize(_.debounce(adjustControlsWidth, 400));
		setTimeout(function () {
			adjustControlsWidth(true);
		}, 200);

		//@Fack this
		$(document).on('click', '#app-navigation-toggle', function () {
			setTimeout(function () {
				if ($('#app-content').css('transform').toString().indexOf('matrix') >= 0) {
					$('#passman-controls').css('width', 'calc( 100% - 245px)');
					$('#passman-controls').css('top', '0');
				} else {
					$('#passman-controls').css('left', 0);
					$('#passman-controls').css('top', '44px');
					$('#passman-controls').css('width', '100%');
				}
			}, 350);
		});
	});
}());