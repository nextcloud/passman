(function() {
	'use strict';

	/* global _ */

	/**
	 * @ngdoc directive
	 * @name ngPasswordMeter.directive:ngPassworMeter
	 * @description
	 * Simple and elegant password strength meter from the Lifekees
	 * Password Manager
	 */
	angular.module('ngPasswordMeter', [])
		.directive('ngPasswordMeter', function() {
			return {
				templateUrl: 'views/partials/password-meter.html',
				restrict: 'E',
				scope: {
					password: '=',
					strength: '=?',
					score: '=?',
				},
				link: function(scope) {

					scope.scoreShown = false;
					scope.matchBreakdown = false;
					scope.toggleScore = function(){
						scope.scoreShown = !scope.scoreShown;
					}
					scope.toggleMatchBreakdown = function(){
						//scope.matchBreakdown = !scope.matchBreakdown;
						var _dialog_width = ((180 * scope.score.sequence.length) < 720) ? 200 * scope.score.sequence.length : 720;
						jQuery('.match-sequence').dialog({
							width: _dialog_width
						})
					};

					var measureStrength = function(p) {
						console.log();
						if(p){
							var _score = zxcvbn(p)
						}
						console.log(_score.score);
						return _score;
					};

					scope.colClass = '';
					scope.masterClass = '';

					scope.$watch('password', function() {
						scope.first 	= '';
						scope.second 	= '';
						scope.third 	= '';
						scope.fourth 	= '';
						scope.message	= '';

						if (!scope.password) {
							scope.masterClass = 'hidden';
							return;
						}
						var _score = measureStrength(scope.password);
						scope.score = _score;
						scope.strength = _score.score;
						scope.masterClass = '';

						if (scope.strength == 0) {
							scope.first = 'poor';
							scope.message	= 'poor';
						} else if (scope.strength == 1) {
							scope.first = 'poor';
							scope.second = 'poor';
							scope.message	= 'poor';
						} else if (scope.strength == 2) {
							scope.first = 'weak';
							scope.second = 'weak';
							scope.message	= 'weak';
						} else if (scope.strength == 3) {
							scope.first = 'good';
							scope.second = 'good';
							scope.third = 'good';
							scope.message	= 'good';
						} else if (scope.strength == 4) {
							scope.first = 'strong';
							scope.second = 'strong';
							scope.third = 'strong';
							scope.fourth = 'strong';
							scope.message	= 'strong';
						}

					});
				},
			};
		});
})();