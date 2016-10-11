var _config = {
	specs: ['tests/js/create_vault.js'],
	jasmineNodeOpts: {
		print: function () {
		}
	},
	onPrepare: function () {
		var SpecReporter = require('jasmine-spec-reporter');
		// add jasmine spec reporter
		jasmine.getEnv().addReporter(new SpecReporter({displayStacktrace: 'all'}));
	}
};

if (process.env.TRAVIS) {
	_config.sauceUser = process.env.SAUCE_USERNAME;
	_config.sauceKey = process.env.SAUCE_ACCESS_KEY;
	_config.capabilities = {
		'browserName': 'chrome',
		'tunnel-identifier': process.env.TRAVIS_JOB_NUMBER,
		'build': process.env.TRAVIS_BUILD_NUMBER
	};
} else {
	_config.capabilities = {
		'browserName': 'firefox', // or 'safari'
		'tunnel-identifier': process.env.TRAVIS_JOB_NUMBER,
		'build': process.env.TRAVIS_BUILD_NUMBER
	}
}


exports.config = _config;
