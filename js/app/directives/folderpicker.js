/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2018, Felix Nüsse (felix.nuesse@t-online.de)
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
   * @ngdoc directive
   * @name passmanApp.directive:folderpicker
   * @description
   * # folderpicker
   */
  angular.module('passmanApp').directive('folderPicker', [
	  '$window', '$http', 'CredentialService', 'NotificationService', '$translate', '$rootScope', function($window, $http, CredentialService, NotificationService, $translate, $rootScope) {
      return {
          templateUrl: 'views/partials/folder-picker.html',
          restrict: 'A',
          scope: {
              credential: '=folderPicker'
          },
          link: function(scope, element) {

			  scope.save = function() {
				  CredentialService.updateCredential(scope.credential).then(function () {
					  NotificationService.showNotification($translate.instant('folderpath.moved'), 5000);
					  $rootScope.$broadcast('updateFolderInMainList', scope.credential);
					  $('#folderPicker').dialog('close');
				  });
			  };



          $(element).click(function() {
              $('#folderPicker').dialog({
                  width: 400,
                  height: 140,
                  close: function() {
                      $(this).dialog('destroy');
                  }
              });
          });
          }
      };
  }]);
}());
