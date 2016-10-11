exports.config = {
	seleniumAddress: 'http://localhost:4444/wd/hub',
	specs: ['tests/js/create_vault.js'],
	capabilities: {
		'browserName': 'firefox' // or 'safari'
	},
	onPrepare: function() {
		var SpecReporter = require('jasmine-spec-reporter');
		// add jasmine spec reporter
		jasmine.getEnv().addReporter(new SpecReporter({displayStacktrace: 'all'}));
	},
	print: function() {}
};
