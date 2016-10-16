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


