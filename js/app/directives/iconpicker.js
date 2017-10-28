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
  angular.module('passmanApp').directive('iconPicker', [
    '$window', 'IconService', '$http', function($window, IconService, $http) {
      return {
        templateUrl: 'views/partials/icon-picker.html',
        restrict: 'A',
        scope: {
          credential: '=iconPicker'
        },
        link: function(scope, element) {

          IconService.getIcons().then(function(icons) {
            scope.icons = icons;
          });

          scope.selectIcon = function(icon) {
            scope.selectedIcon = icon;
          };

          scope.useIcon = function() {
            $http.get(scope.selectedIcon.url).then(function(result) {
              var base64Data = window.btoa(result.data);
              var mimeType = 'svg+xml';
              if(!scope.credential.icon){
                scope.credential.icon = {};
              }
              scope.credential.icon.type = mimeType;
              scope.credential.icon.content = base64Data;
              $('#iconPicker').dialog('close');
            });
          };

          $(element).click(function() {
            $('#iconPicker').dialog({
              width: 800,
              height: 380,
              close: function() {
                $(this).dialog('destroy');
              }
            });
          });
        }
      };
    }]);
}());