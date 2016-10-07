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
		.config(function (jQueryrouteProvider) {
			jQueryrouteProvider
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
		}).config(['jQueryhttpProvider', function (jQueryhttpProvider) {
		jQueryhttpProvider.defaults.headers.common.requesttoken = oc_requesttoken;
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
			var credential = findItemByID(jQuery(this).attr('data-item-id'));
			angular.element('#app-content-wrapper').scope().recoverCredential(credential);
			//Outside anglular we need jQueryapply
			angular.element('#app-content-wrapper').scope().jQueryapply();
		});
		jQuery(document).on('click', '.undoRestore', function () {
			var credential = findItemByID(jQuery(this).attr('data-item-id'));
			angular.element('#app-content-wrapper').scope().deleteCredential(credential);
			//Outside anglular we need jQueryapply
			angular.element('#app-content-wrapper').scope().jQueryapply();
		});
		var adjustControlsWidth = function (r) {
			if (jQuery('#controls').length) {
				var controlsWidth;
				// if there is a scrollbar …
				if (jQuery('#app-content').get(0)) {
					if (jQuery('#app-content').get(0).scrollHeight > jQuery('#app-content').height()) {
						if (jQuery(window).width() > 768) {
							controlsWidth = jQuery('#content').width() - jQuery('#app-navigation').width() - OC.Util.getScrollBarWidth();
							if (!jQuery('#app-sidebar').hasClass('hidden') && !jQuery('#app-sidebar').hasClass('disappear')) {
								controlsWidth -= jQuery('#app-sidebar').width();
							}
						} else {
							controlsWidth = jQuery('#content').width() - OC.Util.getScrollBarWidth();
						}
					} else { // if there is none
						if (jQuery(window).width() > 768) {
							controlsWidth = jQuery('#content').width() - jQuery('#app-navigation').width();
							if (!jQuery('#app-sidebar').hasClass('hidden') && !jQuery('#app-sidebar').hasClass('disappear')) {
								//controlsWidth -= jQuery('#app-sidebar').width();
							}
						} else {
							controlsWidth = jQuery('#content').width();
						}
					}
				}
				var magic;
				if (r) {
					magic = 0;
				} else {
					magic = 85;
				}
				jQuery('#controls').css('width', controlsWidth + magic);
				jQuery('#controls').css('min-width', controlsWidth + magic);
			}
		};
		jQuery(window).resize(_.debounce(adjustControlsWidth, 400));
		setTimeout(function () {
			adjustControlsWidth(true);
		}, 200);

		//@Fack this
		jQuery(document).on('click', '#app-navigation-toggle', function () {
			setTimeout(function () {
				if (jQuery('#app-content').css('transform').toString().indexOf('matrix') >= 0) {
					jQuery('#passman-controls').css('width', 'calc( 100% - 245px)');
					jQuery('#passman-controls').css('top', '0');
				} else {
					jQuery('#passman-controls').css('left', 0);
					jQuery('#passman-controls').css('top', '44px');
					jQuery('#passman-controls').css('width', '100%');
				}
			}, 350);
		});
	});
}());