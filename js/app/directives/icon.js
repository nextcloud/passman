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
   * @ngdoc directive
   * @name passmanApp.directive:passwordGen
   * @description
   * # passwordGen
   */
  angular.module('passmanApp').directive('credentialIcon', [
    '$window', function($window) {
      return {
        template: '<img ng-src="{{ iconUrl }}" class="icon-image">',
        restrict: 'E',
        scope: {
          credential: '=',
        },
        link: function(scope, element) {
          if (!scope.credential) {
            return;
          }
          scope.$watch('credential', function() {
            if(scope.credential.icon && scope.credential.icon.type){
              scope.iconUrl = 'data:image/'+ scope.credential.icon.type +';base64,' + scope.credential.icon.content;
            } else {
              if(scope.credential.url) {
                var url = window.btoa(angular.copy(scope.credential.url)).replace('/', '_');
                scope.iconUrl = OC.generateUrl('apps/passman/api/v2/icon/') + url + '/' +
                    scope.credential.credential_id;
              }
            }
          }, true);
        }
      };
    }]);
}());