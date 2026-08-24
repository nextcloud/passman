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
	PassmanImporter.keepassCsv = {
		info: {
			name: 'KeePass csv',
			id: 'keepassCsv',
			exportSteps: ['If using Keepass V1: Create an csv export with the following options enabled: http://i.imgur.com/CaeTA4d.png', 'With Keepass V2 or Keepass XC no configuration is needed']
		}
	};

	var addTag = function (tags, name) {
		var text = (name === undefined || name === null) ? '' : String(name).trim();
		if (text.length === 0) {
			return;
		}
		for (var t = 0; t < tags.length; t++) {
			if (tags[t].text === text) {
				return;
			}
		}
		tags.push({text: text});
	};

	var parseGroups = function (row) {
		var tags = [];
		var group_paths = [row.group, row.group_tree];
		for (var g = 0; g < group_paths.length; g++) {
			if (!group_paths[g]) {
				continue;
			}
			var exploded_tree = String(group_paths[g]).split(/[\\/]+/);
			for (var t = 0; t < exploded_tree.length; t++) {
				addTag(tags, exploded_tree[t]);
			}
		}
		return tags;
	};

	var getLabel = function (row) {
		return row.account || row.title || row.web_site || row.url || null;
	};

	var parseOtp = function (row) {
		var raw = row.totp || row.otp || row.totp_seed;
		if (raw === undefined || raw === null) {
			return null;
		}
		var value = String(raw).trim();
		if (value.length === 0) {
			return null;
		}

		if (value.toLowerCase().indexOf('otpauth://') !== 0) {
			return {
				secret: value
			};
		}

		try {
			/** global: URL */
			var uri = new URL(value);
			var type = (uri.href.toLowerCase().indexOf('totp/') !== -1) ? 'totp' : 'hotp';
			var labelPath = uri.pathname.replace(/^\/+/, '');
			if (labelPath.toLowerCase().indexOf(type + '/') === 0) {
				labelPath = labelPath.substring(type.length + 1);
			}
			var secret = uri.searchParams.get('secret');
			if (!secret) {
				return null;
			}
			return {
				type: type,
				label: decodeURIComponent(labelPath),
				issuer: uri.searchParams.get('issuer'),
				secret: secret,
				algorithm: uri.searchParams.get('algorithm') ? uri.searchParams.get('algorithm') : 'SHA1',
				period: uri.searchParams.get('period') ? parseInt(uri.searchParams.get('period'), 10) : 30,
				digits: uri.searchParams.get('digits') ? parseInt(uri.searchParams.get('digits'), 10) : 6,
				qr_uri: {
					image: '',
					qrData: value
				}
			};
		} catch (e) {
			var secretMatch = /[?&]secret=([^&]+)/i.exec(value);
			if (!secretMatch) {
				return null;
			}
			return {
				secret: decodeURIComponent(secretMatch[1]),
				qr_uri: {
					image: '',
					qrData: value
				}
			};
		}
	};

	PassmanImporter.keepassCsv.readFile = function (file_data) {
		/** global: C_Promise */
		var p = new C_Promise(function () {
			var parsed_csv = PassmanImporter.readCsv(file_data).filter(function (row) {
				// don't import if a label is missing or can't be derived from other fields
				return getLabel(row) !== null;
			});
			var credential_list = [];
			for (var i = 0; i < parsed_csv.length; i++) {
				var row = parsed_csv[i];
				var _credential = PassmanImporter.newCredential();
				_credential.label = getLabel(row);
				_credential.username = row.login_name || row.username || null;
				_credential.password = row.password || null;
				_credential.url = row.web_site || row.url || null;
				_credential.description = row.comments || row.notes || null;
				if (row.expires) {
					var expire_time = new Date(String(row.expires).replace(/"/g, '')).getTime();
					if (!isNaN(expire_time)) {
						_credential.expire_time = expire_time / 1000;
					}
				}

				_credential.tags = parseGroups(row);

				var otp = parseOtp(row);
				if (otp) {
					_credential.otp = otp;
				}

				credential_list.push(_credential);

				var progress = {
					percent: (i + 1) / parsed_csv.length * 100,
					loaded: i + 1,
					total: parsed_csv.length
				};
				this.call_progress(progress);
			}
			this.call_then(credential_list);
		});
		return p;
	};
})(window, $, PassmanImporter);
