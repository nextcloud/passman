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

(function () {
	'use strict';
	/**
	 * @ngdoc directive
	 * @name passmanApp.directive:clearBtn
	 * @description
	 * # clearBtn
	 */
	angular.module('passmanApp')
		.directive('clearBtn', ['$parse', function ($parse) {
            return {
                link: function (scope, elm, attr) {
                    elm.wrap("<div style='position: relative'></div>");
                    var btn = '<span id=' + Math.round(Math.random() * 1000000000) + ' class="searchclear ng-hide fa fa-times-circle-o"></span>';
                    var angularBtn = angular.element(btn);
                    elm.after(angularBtn);
                    //clear the input
                    angularBtn.on("click", function () {
                        elm.val('').trigger("change");
                        $parse(attr.ngModel).assign(scope, '');
                        scope.$apply();
                    });

                    // show  clear btn  on focus
                    elm.bind('focus keyup change paste propertychange', function () {
                        if (elm.val() && elm.val().length > 0) {
                            angularBtn.removeClass("ng-hide");
                        } else {
                            angularBtn.addClass("ng-hide");
                        }
                    });
                }
            };
        }]);
}());


