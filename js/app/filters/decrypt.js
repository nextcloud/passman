'use strict';

/**
 * @ngdoc filter
 * @name passmanApp.filter:decrypt
 * @function
 * @description
 * # decrypt
 * Filter in the passmanApp.
 */
angular.module('passmanApp')
  .filter('decrypt',['EncryptService', function (EncryptService) {
    return function (input) {
        if(input) {
            var string =  EncryptService.decryptString(input).toString();
            console.log(string);
            return string;
        }
    };
  }]);
