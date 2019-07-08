// Karma configuration
// Generated on Mon Oct 17 2016 15:46:52 GMT+0200 (CEST)
var isTravis = (process.env.TRAVIS_BUILD_NUMBER) ? true : false;
var browsers = ['Firefox'];
if(!isTravis){
	browsers = ['Chrome'];
}
module.exports = function (config) {
	config.set({

		// base path that will be used to resolve all patterns (eg. files, exclude)
		basePath: '.',


		// frameworks to use
		// available frameworks: https://npmjs.org/browse/keyword/karma-adapter
		frameworks: ['jasmine'],


		// list of files / patterns to load in the browser
		files: [
			'../../core/vendor/jquery/dist/jquery.js',
			'../../core/vendor/underscore/underscore-min.js',
			'js/vendor/angular/angular.min.js',
			'tests/unit/js/mocks/*.js',
			'js/vendor/angular-mocks/angular-mocks.js',
			'js/vendor/angular-translate/angular-translate.min.js',
			'js/vendor/**/*.js',
			'js/app/**/*.js',
			'js/lib/**/*.js',
			{ pattern: 'tests/unit/js/app/**/*.js', included: true }
		],


		// list of files to exclude
		exclude: [
			'js/vendor/angular-mocks/ngAnimateMock.js',
			'js/vendor/angular-mocks/ngMock.js',
			'js/vendor/angular-mocks/ngMockE2E.js'
		],


		// preprocess matching files before serving them to the browser
		// available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
		preprocessors: {},


		// test results reporter to use
		// possible values: 'dots', 'progress'
		// available reporters: https://npmjs.org/browse/keyword/karma-reporter
		reporters: ['verbose'],


		// web server port
		port: 9876,


		// enable / disable colors in the output (reporters and logs)
		colors: true,

    browserNoActivityTimeout : 60000,//by default 10000
		// level of logging
		// possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
		logLevel: config.LOG_INFO,


		// enable / disable watching file and executing tests whenever any file changes
		autoWatch: false,


		// start these browsers
		// available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
		browsers: browsers,


		// Continuous Integration mode
		// if true, Karma captures browsers, runs the tests and exits
		singleRun: true
	});
};
