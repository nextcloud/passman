/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Importers should always start with this
/** global: PassmanImporter */
var PassmanImporter = PassmanImporter || {};
(function (window, $, PassmanImporter) {
	'use strict';

// Define the importer
	PassmanImporter.randomData = {
		info: {
			name: 'Random data',
			id: 'randomData',
			exportSteps: ['Create\'s 50 random credentials for testing purposes.']
		}
	};

	// The random data is generated locally; no server or third party request is needed.
	// Since this is basically only a showcase importer, it doesn't matter at all.
	const firstNames = ['alice', 'bob', 'carol', 'dave', 'erin', 'frank', 'grace', 'heidi', 'ivan', 'judy',
		'mallory', 'niaj', 'olivia', 'peggy', 'rupert', 'sybil', 'trent', 'victor', 'walter', 'zoe'];
	const lastNames = ['smith', 'miller', 'meyer', 'jones', 'brown', 'wilson', 'taylor', 'clark', 'lewis',
		'walker', 'hall', 'young', 'king', 'wright', 'scott', 'green', 'baker', 'adams', 'nelson', 'hill'];
	const domainWords = ['blue', 'red', 'green', 'fast', 'cloud', 'web', 'net', 'data', 'shop', 'mail',
		'host', 'soft', 'micro', 'star', 'sun', 'moon', 'tech', 'apps', 'bank', 'game'];
	const tlds = ['com', 'net', 'org', 'de', 'io', 'eu', 'info', 'dev', 'local'];
	const passwordChars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*()-_=+€';

	// uniform random integer in [0, max)
	var randomInt = function (max) {
		var buffer = new Uint32Array(1);
		var limit = Math.floor(0x100000000 / max) * max;
		do {
			window.crypto.getRandomValues(buffer);
		} while (buffer[0] >= limit);
		return buffer[0] % max;
	};

	var pick = function (list) {
		return list[randomInt(list.length)];
	};

	var insecureRandomPassword = function (length) {
		var password = '';
		for (var i = 0; i < length; i++) {
			password += passwordChars.charAt(randomInt(passwordChars.length));
		}
		return password;
	};

	var generatePerson = function () {
		var domain = pick(domainWords) + pick(domainWords) + '.' + pick(tlds);
		var username = pick(firstNames) + '.' + pick(lastNames) + randomInt(100);
		return {
			domain: domain,
			email_u: username,
			email_d: domain,
			username: username,
			password: insecureRandomPassword(16),
			url: 'https://' + domain + '/'
		};
	};

	PassmanImporter.randomData.readFile = function () {
		/** global: C_Promise */
		return new C_Promise(function () {
			var tags =
				['Social media',
					'Hosting',
					'Forums',
					'Webshops',
					'FTP',
					'SSH',
					'Banking',
					'Applications',
					'Server stuff',
					'mysql',
					'Wifi',
					'Games',
					'Certificate',
					'Serials'
				];
			var max = 50;
			var credential_list = [];

			for (var i = 1; i <= max; i++) {
				var data = generatePerson();
				var _credential = PassmanImporter.newCredential();
				_credential.label = (randomInt(2) === 0) ? data.domain : data.email_d + ' - ' + data.email_u;
				_credential.username = data.username;
				_credential.password = data.password;
				_credential.url = data.url;

				var tag_amount = randomInt(5);
				var _tags = [];
				while (_tags.length < tag_amount) {
					var item = pick(tags);
					if (_tags.indexOf(item) === -1) {
						_tags.push(item);
					}
				}
				_credential.tags = _tags.map(function (item) {
					return {text: item};
				});
				credential_list.push(_credential);

				this.call_progress({
					percent: i / max * 100,
					loaded: i,
					total: max
				});
			}

			this.call_then(credential_list);
		});
	};
})(window, $, PassmanImporter);
