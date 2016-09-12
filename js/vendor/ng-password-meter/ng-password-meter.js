(function () {
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
		.directive('ngPasswordMeter', ['$window', function ($window) {
			return {
				templateUrl: 'views/partials/password-meter.html',
				restrict: 'E',
				scope: {
					password: '=',
					strength: '=?',
					score: '=?',
				},
				link: function (scope) {

					scope.scoreShown = false;
					scope.matchBreakdown = false;
					scope.toggleScore = function () {
						scope.scoreShown = !scope.scoreShown;
					}
					scope.toggleMatchBreakdown = function () {
						var width = ($window.innerWidth > 420) ? $window.innerWidth * 0.85 : $window.innerWidth * 0.8;
						var ms_elem = jQuery('.match-sequence')
						ms_elem.dialog({
							title: 'Password breakdown',
							width: width,
							open: function () {
								var _totalWidth = 0;

								jQuery('.match-sequence').find('.sequence').each(function (key, el) {
									_totalWidth += jQuery(el).width()+20;
								})
								console.log(_totalWidth, $window.innerWidth * 0.85)
								if (_totalWidth < $window.innerWidth * 0.85) {
									ms_elem.dialog("option", "width", _totalWidth);
									jQuery('.ui-dialog').position({
										my: "center",
										at: "center",
										of: window,
										collision: "fit",
										// Ensure the titlebar is always visible
										using: function (pos) {
											var topOffset = $(this).css(pos).offset().top;
											if (topOffset < 0) {
												$(this).css("top", pos.top - topOffset);
											}
										}
									});
								}
								jQuery('.match-sequence').find('.container').width(_totalWidth);
							}

						})
					};

					var measureStrength = function (p) {
						if (p) {
							var _score = zxcvbn(p)
						}
						return _score;
					};

					scope.colClass = '';
					scope.masterClass = '';

					scope.$watch('password', function () {
						scope.first = '';
						scope.second = '';
						scope.third = '';
						scope.fourth = '';
						scope.message = '';

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
							scope.message = 'poor';
						} else if (scope.strength == 1) {
							scope.first = 'poor';
							scope.second = 'poor';
							scope.message = 'poor';
						} else if (scope.strength == 2) {
							scope.first = 'weak';
							scope.second = 'weak';
							scope.message = 'weak';
						} else if (scope.strength == 3) {
							scope.first = 'good';
							scope.second = 'good';
							scope.third = 'good';
							scope.message = 'good';
						} else if (scope.strength == 4) {
							scope.first = 'strong';
							scope.second = 'strong';
							scope.third = 'strong';
							scope.fourth = 'strong';
							scope.message = 'strong';
						}

					});
				},
			};
		}]);
})();