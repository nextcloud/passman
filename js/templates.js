angular.module('templates-main', ['views/main.html']);

angular.module("views/main.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("views/main.html",
    "Hello world!");
}]);
