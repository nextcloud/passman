'use strict';

/**
 * @ngdoc filter
 * @name passmanApp.filter:selectedTags
 * @function
 * @description
 * # selectedTags
 * Filter in the passmanApp.
 */
angular.module('passmanApp')
	.filter('credentialSearch', function () {
		return function (credentials, filter) {
			var _credentials = [];
			if(credentials) {
				if (filter.filterText == "") {
					return credentials
				}

				for (var ci = 0; ci < credentials.length; ci++) {
					var c = credentials[ci];
					for(var f = 0; f < filter.fields.length; f++){
						var field = filter.fields[f];
						if(typeof c[field] === 'string' ){
							if(c[field].indexOf(filter.filterText) >= 0){
								_credentials.push(c);
								break;
							}
						} else {
							var t = JSON.stringify(c[field]);
							if(t.indexOf(filter.filterText) >= 0){
								_credentials.push(c);
								break;
							}
						}
					}
				}
				return _credentials;
			}
		};
	});