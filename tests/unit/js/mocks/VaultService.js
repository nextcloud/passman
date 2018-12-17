angular.module('mock.vaultService', []).
service('VaultService', function($q) {
	var vaultService = {};
	var settings = {};
    vaultService.getVaultSetting = function (key, default_value) {
        if (settings[key]) {
            return $q.when(settings[key]);
        } else {
            return $q.when(default_value);
        }
    };
    vaultService.setVaultSetting = function (key, value) {
        settings[key]=value;
    };

	return vaultService;
});
