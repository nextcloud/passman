/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function() {
  'use strict';

  /**
   * @ngdoc overview
   * @name passmanApp
   * @description
   * # passmanApp
   *
   * Main module of the application.
   */
  angular.module('passmanApp', [
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
    'angularjs-datetime-picker',
    'ui.sortable',
    'pascalprecht.translate',
  ]).config(function($routeProvider) {
    $routeProvider.when('/', {
      templateUrl: 'views/vaults.html',
      controller: 'VaultCtrl',
    }).when('/vault/:vault_id', {
      templateUrl: 'views/show_vault.html',
      controller: 'CredentialCtrl',
    }).when('/vault/:vault_id/new', {
      templateUrl: 'views/edit_credential.html',
      controller: 'CredentialEditCtrl',
    }).when('/vault/:vault_id/edit/:credential_id', {
      templateUrl: 'views/edit_credential.html',
      controller: 'CredentialEditCtrl',
    }).when('/vault/:vault_id/:credential_id/share', {
      templateUrl: 'views/share_credential.html',
      controller: 'ShareCtrl',
    }).when('/vault/:vault_id/:credential_id/revisions', {
      templateUrl: 'views/credential_revisions.html',
      controller: 'RevisionCtrl',
    }).when('/vault/:vault_id/request-deletion', {
      templateUrl: 'views/vault_req_deletion.html',
      controller: 'RequestDeleteCtrl',
    }).when('/vault/:vault_id/settings', {
      templateUrl: 'views/settings.html',
      controller: 'SettingsCtrl',
    }).otherwise({
      redirectTo: '/',
    });
  }).config([
    '$httpProvider', function($httpProvider) {
      /** global: oc_requesttoken */
      $httpProvider.defaults.headers.common.requesttoken = oc_requesttoken;
    }]).config(function(localStorageServiceProvider) {
    localStorageServiceProvider.setNotify(true, true);
  }).config(function($translateProvider) {
    $translateProvider.useSanitizeValueStrategy('sanitizeParameters');
    $translateProvider.useUrlLoader(OC.generateUrl('/apps/passman/api/v2/language'));
    $translateProvider.preferredLanguage('en');
  }).run([
    '$rootScope', function($rootScope) {
      $rootScope.$on('$routeChangeSuccess', function(e, curr, prev) {
        $('.ui-dialog-content').dialog('close');
      });
    }]);

  /**
   * jQuery for notification handling D:
   **/
  jQuery(document).ready(function() {
    var findItemByID = function(id) {
      var credentials, foundItem = false;
      credentials = angular.element('#app-content-wrapper').scope().credentials;
      angular.forEach(credentials, function(credential) {
        if (credential.credential_id === id) {
          foundItem = credential;
        }
      });
      return foundItem;
    };
    jQuery(document).on('click', '.undoDelete', function() {
      var credential = findItemByID($(this).attr('data-item-id'));
      angular.element('#app-content-wrapper').scope().recoverCredential(credential);
      //Outside anglular we need $apply
      angular.element('#app-content-wrapper').scope().$apply();
    });
    jQuery(document).on('click', '.undoRestore', function() {
      var credential = findItemByID($(this).attr('data-item-id'));
      angular.element('#app-content-wrapper').scope().deleteCredential(credential);
      //Outside anglular we need $apply
      angular.element('#app-content-wrapper').scope().$apply();
    });
  });
}());