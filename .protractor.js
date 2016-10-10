exports.config = {
	seleniumAddress: 'http://localhost:4444/wd/hub',
	specs: ['tests/js/create_vault.js'],
	capabilities: {
		'browserName': 'firefox' // or 'safari'
	}
};
