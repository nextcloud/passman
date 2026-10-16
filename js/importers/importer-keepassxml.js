/**
 * Nextcloud - Passman
 *
 * @copyright 2026 Timo Triebensky (timo@binsky.org)
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

/** global: PassmanImporter */
var PassmanImporter = PassmanImporter || {};
(function (window, $, PassmanImporter) {
	'use strict';

	PassmanImporter.keepassXml = {
		info: {
			name: 'KeePass xml',
			id: 'keepassXml',
			exportSteps: [
				'KeePass 2: File → Export → KeePass XML (2.x). This export includes attachments and history.',
				'KeePassXC: Database → Export → KeePassXC XML File. History is included; attachments usually not included. But they are imported when the XML contains a Binaries section.',
				'KeePass 1 / KeePassX XML exports are also accepted (no attachments or history).'
			]
		}
	};

	let FileService = null;
	let EncryptService = null;

	PassmanImporter.keepassXml.setRequiredServices = function (FileSvc, EncryptSvc) {
		FileService = FileSvc;
		EncryptService = EncryptSvc;
	};

	const STANDARD_STRING_KEYS = {
		title: true,
		username: true,
		password: true,
		url: true,
		notes: true
	};

	const EMAIL_KEYS = {
		email: true,
		'e-mail': true,
		emailaddress: true
	};

	const OTP_META_KEYS = {
		otp: true,
		otpauth: true,
		totp: true,
		'totp seed': true,
		'totpseeds': true,
		'timeotp-secret': true,
		'timeotp-secret-base32': true,
		'timeotp-secret-hex': true,
		'timeotp-secret-base64': true,
		'timeotp-length': true,
		'timeotp-period': true,
		'timeotp-algorithm': true,
		'timeotp-settings': true,
		'hmacotp-secret': true,
		'hmacotp-secret-base32': true,
		'hmacotp-secret-hex': true,
		'hmacotp-secret-base64': true,
		'hmacotp-counter': true
	};

	/**
	 * Since the MIME type is not available in the XML file, we need to map the file extension to the usual default MIME type.
	 */
	const MIME_BY_EXTENSION = {
		png: 'image/png',
		jpg: 'image/jpeg',
		jpeg: 'image/jpeg',
		gif: 'image/gif',
		webp: 'image/webp',
		svg: 'image/svg+xml',
		ico: 'image/x-icon',
		pdf: 'application/pdf',
		txt: 'text/plain',
		csv: 'text/csv',
		json: 'application/json',
		xml: 'application/xml',
		html: 'text/html',
		zip: 'application/zip',
		gz: 'application/gzip',
		kdbx: 'application/octet-stream'
	};

	const YEAR1_TO_UNIX_SECONDS = 62135596800;
	const NULL_UUID = 'AAAAAAAAAAAAAAAAAAAAAA==';

	const tagName = function (el) {
		if (!el) {
			return '';
		}
		return (el.localName || el.tagName || '').toLowerCase();
	};

	/**
	 * Extract all direct children of the given XML element with the given tag name.
	 */
	const directChildren = function (el, name) {
		var matches = [];
		if (!el) {
			return matches;
		}
		var wanted = name.toLowerCase();
		var kids = el.children || el.childNodes;
		for (var i = 0; i < kids.length; i++) {
			var child = kids[i];
			if (child.nodeType && child.nodeType !== 1) {
				continue;
			}
			if (tagName(child) === wanted) {
				matches.push(child);
			}
		}
		return matches;
	};

	/**
	 * Extract the first direct child of the given XML element with the given tag name.
	 */
	const firstChild = function (el, name) {
		var matches = directChildren(el, name);
		return matches.length ? matches[0] : null;
	};

	/**
	 * Extract the text content of the given XML element.
	 */
	const textOf = function (el, trim) {
		if (!el) {
			return '';
		}
		var value = el.textContent || '';
		return trim === false ? value : value.trim();
	};

	/**
	 * Extract the value of the given attribute of the given XML element.
	 */
	const attrOf = function (el, name) {
		if (!el || !el.getAttribute) {
			return '';
		}
		return el.getAttribute(name) || '';
	};

	/**
	 * Basically just a normalized string comparison to 'true'.
	 */
	const isXmlTrue = function (value) {
		return String(value).toLowerCase() === 'true';
	};

	/**
	 * Add a tag to the given array of tags if it is not already present.
	 */
	const addTag = function (tags, name) {
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

	/**
	 * Parse the KeePass time string to a Unix timestamp. It seems it usually uses base64 encoded binary representation of the timestamp.
	 * ```
	 * Example: binary = window.atob('wVce4g4AAAA=')    => "ÁW\u001eâ\u000e\u0000\u0000\u0000"
	 *          packed = 0;
	 *          for (var i = 7; i >= 0; i--) {
	 *              packed = packed * 256 + (binary.charCodeAt(i) & 0xff);
	 *          }                                       => 63923181505
	 *          packed - YEAR1_TO_UNIX_SECONDS          => 1787584705 => Mon Aug 24 2026 15:18:25 GMT+0000
	 * ```
	 * @param {string} value The KeePass time string.
	 * @returns {number|null} The Unix timestamp or null if the string is invalid.
	 */
	const parseKeePassTime = function (value) {
		if (value === undefined || value === null) {
			return null;
		}
		var raw = String(value).trim();
		if (raw.length === 0 || raw.toLowerCase() === 'never') {
			return null;
		}

		if (/^\d{4}-\d{2}-\d{2}T/.test(raw)) {
			var iso = Date.parse(raw);
			return isNaN(iso) ? null : Math.floor(iso / 1000);
		}

		try {
			var binary = window.atob(raw.replace(/\s+/g, ''));
			if (binary.length !== 8) {
				return null;
			}
			var packed = 0;
			for (var i = 7; i >= 0; i--) {
				packed = packed * 256 + (binary.charCodeAt(i) & 0xff);
			}
			if (packed === 0) {
				return null;
			}
			return packed - YEAR1_TO_UNIX_SECONDS;
		} catch (e) {
			var fallback = Date.parse(raw);
			return isNaN(fallback) ? null : Math.floor(fallback / 1000);
		}
	};

	const parseOtpAuth = function (raw) {
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

	const normalizeOtpAlgorithm = function (value) {
		if (!value) {
			return 'SHA1';
		}
		var raw = String(value).toUpperCase().replace(/HMAC-?/g, '').replace(/[^A-Z0-9]/g, '');
		if (raw.indexOf('SHA256') !== -1) {
			return 'SHA256';
		}
		if (raw.indexOf('SHA512') !== -1) {
			return 'SHA512';
		}
		return 'SHA1';
	};

	const getField = function (fields, name) {
		var wanted = name.toLowerCase();
		for (var key in fields) {
			if (fields.hasOwnProperty(key) && key.toLowerCase() === wanted) {
				return fields[key];
			}
		}
		return null;
	};

	const getFieldValue = function (fields, names) {
		for (var i = 0; i < names.length; i++) {
			var field = getField(fields, names[i]);
			if (field && field.value) {
				return field.value;
			}
		}
		return '';
	};

	/**
	 * Parse the OTP from the given fields (supports TOTP and HOTP).
	 *
	 * @param {Object} fields The fields to parse.
	 * @returns {Object|null} The OTP object or null if no OTP is found.
	 *
	 * The OTP object has the following properties:
	 * - type: 'hotp' or 'totp'
	 * - secret: The secret key
	 * - counter: The counter value (defaults to 0; only for hotp)
	 * - algorithm: The algorithm used (defaults to SHA1)
	 * - period: The period value (defaults to 30; only for totp)
	 * - digits: The digits value (defaults to 6; only for totp)
	 */
	const parseOtpFromFields = function (fields) {
		var otpauth = getFieldValue(fields, ['otp', 'otpauth', 'totp']);
		if (otpauth && otpauth.toLowerCase().indexOf('otpauth://') === 0) {
			return parseOtpAuth(otpauth);
		}

		var hotpSecret = getFieldValue(fields, ['HmacOtp-Secret-Base32', 'HmacOtp-Secret', 'HmacOtp-Secret-Hex', 'HmacOtp-Secret-Base64']);
		if (hotpSecret) {
			var counterField = getField(fields, 'HmacOtp-Counter');
			return {
				type: 'hotp',
				secret: hotpSecret,
				counter: counterField ? parseInt(counterField.value, 10) : 0,
				algorithm: 'SHA1'
			};
		}

		var totpSecret = getFieldValue(fields, [
			'TimeOtp-Secret-Base32',
			'TimeOtp-Secret',
			'TimeOtp-Secret-Hex',
			'TimeOtp-Secret-Base64',
			'TOTP Seed',
			'otp',
			'totp'
		]);
		if (!totpSecret) {
			return null;
		}

		var digitsField = getField(fields, 'TimeOtp-Length');
		var periodField = getField(fields, 'TimeOtp-Period');
		var algorithmField = getField(fields, 'TimeOtp-Algorithm');
		return {
			type: 'totp',
			secret: totpSecret,
			algorithm: normalizeOtpAlgorithm(algorithmField ? algorithmField.value : ''),
			period: periodField ? parseInt(periodField.value, 10) : 30,
			digits: digitsField ? parseInt(digitsField.value, 10) : 6
		};
	};

	const isConsumedStringKey = function (key) {
		var lower = key.toLowerCase();
		return STANDARD_STRING_KEYS[lower] || EMAIL_KEYS[lower] || OTP_META_KEYS[lower];
	};

	const guessMime = function (filename) {
		var parts = String(filename || '').split('.');
		if (parts.length < 2) {
			return 'application/octet-stream';
		}
		var ext = parts.pop().toLowerCase();
		return MIME_BY_EXTENSION[ext] || 'application/octet-stream';
	};

	const base64ToBytes = function (b64) {
		var binary = window.atob(String(b64).replace(/\s+/g, ''));
		var bytes = new Uint8Array(binary.length);
		for (var i = 0; i < binary.length; i++) {
			bytes[i] = binary.charCodeAt(i);
		}
		return bytes;
	};

	const bytesToBase64 = function (bytes) {
		var binary = '';
		var chunk = 0x8000;
		for (var i = 0; i < bytes.length; i += chunk) {
			binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
		}
		return window.btoa(binary);
	};

	const isGzip = function (bytes) {
		return bytes && bytes.length >= 2 && bytes[0] === 0x1f && bytes[1] === 0x8b;
	};

	const gunzipBytes = async function (bytes) {
		if (typeof DecompressionStream === 'undefined') {
			throw new Error('DecompressionStream is not available');
		}
		var stream = new Blob([bytes]).stream().pipeThrough(new DecompressionStream('gzip'));
		var buffer = await new Response(stream).arrayBuffer();
		return new Uint8Array(buffer);
	};

	const decodeBinaryPayload = async function (bytes, compressed) {
		if (!bytes || !bytes.length) {
			return bytes;
		}
		if (compressed || isGzip(bytes)) {
			try {
				return await gunzipBytes(bytes);
			} catch (e) {
				console.error('Failed to decompress KeePass attachment', e);
				return bytes;
			}
		}
		return bytes;
	};

	const collectPooledBinaries = async function (xmlRoot) {
		var pool = {};
		var binariesParents = [];
		if (xmlRoot.getElementsByTagName) {
			var found = xmlRoot.getElementsByTagName('Binaries');
			for (var i = 0; i < found.length; i++) {
				binariesParents.push(found[i]);
			}
		}

		for (var p = 0; p < binariesParents.length; p++) {
			var binaryNodes = directChildren(binariesParents[p], 'Binary');
			for (var b = 0; b < binaryNodes.length; b++) {
				var node = binaryNodes[b];
				var id = attrOf(node, 'ID');
				if (id === '') {
					continue;
				}
				var raw = textOf(node, false);
				if (!raw.trim()) {
					continue;
				}
				var compressed = isXmlTrue(attrOf(node, 'Compressed'));
				pool[id] = await decodeBinaryPayload(base64ToBytes(raw), compressed);
			}
		}
		return pool;
	};

	const uploadAttachment = async function (filename, bytes, cache, cacheKey) {
		if (cacheKey && cache[cacheKey]) {
			return cache[cacheKey];
		}
		if (!FileService || !bytes || !bytes.length) {
			return null;
		}

		var mime = guessMime(filename);
		var file = {
			filename: filename,
			size: bytes.length,
			mimetype: mime,
			data: 'data:' + mime + ';base64,' + bytesToBase64(bytes)
		};

		try {
			var uploaded = await FileService.uploadFile(file);
			if (FileService.getEmptyFileWithDecryptedFilename) {
				uploaded = FileService.getEmptyFileWithDecryptedFilename(uploaded);
			} else if (EncryptService && uploaded && uploaded.filename) {
				delete uploaded.file_data;
				uploaded.filename = EncryptService.decryptString(uploaded.filename);
			}
			if (cacheKey) {
				cache[cacheKey] = uploaded;
			}
			return uploaded;
		} catch (e) {
			console.error('Failed to upload KeePass attachment: ' + filename, e);
			return null;
		}
	};

	const parseStringFields = function (entryEl) {
		var fields = {};
		var nodes = directChildren(entryEl, 'String');
		for (var i = 0; i < nodes.length; i++) {
			var key = textOf(firstChild(nodes[i], 'Key'));
			if (!key) {
				continue;
			}
			var valueEl = firstChild(nodes[i], 'Value');
			if (isXmlTrue(attrOf(valueEl, 'Protected'))) {
				console.warn('Skipping Protected KeePass field (not decryptable in XML): ' + key);
				continue;
			}
			var preserve = key.toLowerCase() === 'password' || key.toLowerCase() === 'notes';
			fields[key] = {
				value: textOf(valueEl, preserve ? false : true),
				secret: isXmlTrue(attrOf(valueEl, 'ProtectInMemory')) || isXmlTrue(attrOf(valueEl, 'Protected'))
			};
		}
		return fields;
	};

	const parseEntryTimes = function (entryEl) {
		var timesEl = firstChild(entryEl, 'Times');
		var times = {
			created: null,
			changed: null,
			expire_time: 0
		};
		if (!timesEl) {
			return times;
		}
		times.created = parseKeePassTime(textOf(firstChild(timesEl, 'CreationTime')));
		times.changed = parseKeePassTime(textOf(firstChild(timesEl, 'LastModificationTime')));
		if (isXmlTrue(textOf(firstChild(timesEl, 'Expires')))) {
			var expiry = parseKeePassTime(textOf(firstChild(timesEl, 'ExpiryTime')));
			if (expiry) {
				times.expire_time = expiry * 1000;
			}
		}
		return times;
	};

	const parseEntryBinaries = async function (entryEl, pool, cache) {
		var files = [];
		var nodes = directChildren(entryEl, 'Binary');
		for (var i = 0; i < nodes.length; i++) {
			var filename = textOf(firstChild(nodes[i], 'Key')) || ('attachment-' + (i + 1));
			var valueEl = firstChild(nodes[i], 'Value');
			if (!valueEl) {
				continue;
			}
			if (isXmlTrue(attrOf(valueEl, 'Protected'))) {
				console.warn('Skipping Protected KeePass attachment: ' + filename);
				continue;
			}

			var ref = attrOf(valueEl, 'Ref');
			var bytes = null;
			var cacheKey = null;
			if (ref !== '') {
				cacheKey = 'ref:' + ref;
				bytes = pool[ref];
				if (!bytes) {
					console.warn('KeePass attachment "' + filename + '" references missing binary ID ' + ref);
					continue;
				}
			} else {
				var raw = textOf(valueEl, false);
				if (!raw.trim()) {
					continue;
				}
				bytes = await decodeBinaryPayload(base64ToBytes(raw), isXmlTrue(attrOf(valueEl, 'Compressed')));
			}

			var uploaded = await uploadAttachment(filename, bytes, cache, cacheKey);
			if (uploaded) {
				files.push(uploaded);
			}
		}
		return files;
	};

	const parseTags = function (entryEl, groupPath) {
		var tags = [];
		for (var g = 0; g < groupPath.length; g++) {
			addTag(tags, groupPath[g]);
		}
		var rawTags = textOf(firstChild(entryEl, 'Tags'));
		if (rawTags) {
			var parts = rawTags.split(/[;,\s]+/);
			for (var t = 0; t < parts.length; t++) {
				addTag(tags, parts[t]);
			}
		}
		return tags;
	};

	const entryToCredential = async function (entryEl, groupPath, pool, cache, includeHistory) {
		var fields = parseStringFields(entryEl);
		var times = parseEntryTimes(entryEl);
		var credential = PassmanImporter.newCredential();
		credential.label = getFieldValue(fields, ['Title']) || null;
		credential.username = getFieldValue(fields, ['UserName']) || null;
		credential.password = getFieldValue(fields, ['Password']) || null;
		credential.url = getFieldValue(fields, ['URL']) || null;
		credential.description = getFieldValue(fields, ['Notes']) || null;
		credential.email = getFieldValue(fields, ['Email', 'E-Mail', 'EMail']) || null;
		credential.tags = parseTags(entryEl, groupPath);
		if (times.created) {
			credential.created = times.created;
		}
		if (times.changed) {
			credential.changed = times.changed;
		}
		credential.expire_time = times.expire_time;

		var otp = parseOtpFromFields(fields);
		if (otp) {
			credential.otp = otp;
		}

		for (var key in fields) {
			if (!fields.hasOwnProperty(key) || isConsumedStringKey(key)) {
				continue;
			}
			if (!fields[key].value) {
				continue;
			}
			credential.custom_fields.push({
				label: key,
				value: fields[key].value,
				secret: !!fields[key].secret,
				field_type: fields[key].secret ? 'password' : 'text'
			});
		}

		credential.files = await parseEntryBinaries(entryEl, pool, cache);

		if (includeHistory) {
			var historyEl = firstChild(entryEl, 'History');
			var historyEntries = historyEl ? directChildren(historyEl, 'Entry') : [];
			var history = [];
			for (var h = 0; h < historyEntries.length; h++) {
				history.push(await entryToCredential(historyEntries[h], groupPath, pool, cache, false));
			}
			history.sort(function (a, b) {
				return (a.changed || a.created || 0) - (b.changed || b.created || 0);
			});
			credential._history = history;
		}

		return credential;
	};

	const skippedGroup = function (groupEl, recycleBinUuid, templatesUuid) {
		var uuid = textOf(firstChild(groupEl, 'UUID'));
		if (uuid && ((recycleBinUuid && uuid === recycleBinUuid) || (templatesUuid && uuid === templatesUuid))) {
			return true;
		}
		return textOf(firstChild(groupEl, 'Name')).toLowerCase() === 'recycle bin';
	};

	const walkGroups = async function (groupEl, parentPath, isRoot, recycleBinUuid, templatesUuid, pool, cache, credentials) {
		if (skippedGroup(groupEl, recycleBinUuid, templatesUuid)) {
			return;
		}

		var name = textOf(firstChild(groupEl, 'Name'));
		var path = parentPath.slice();
		if (name && !isRoot) {
			path.push(name);
		}

		var entries = directChildren(groupEl, 'Entry');
		for (var e = 0; e < entries.length; e++) {
			var credential = await entryToCredential(entries[e], path, pool, cache, true);
			if (credential.label) {
				credentials.push(credential);
			}
		}

		var groups = directChildren(groupEl, 'Group');
		for (var g = 0; g < groups.length; g++) {
			await walkGroups(groups[g], path, false, recycleBinUuid, templatesUuid, pool, cache, credentials);
		}
	};

	const parseKeePassFile = async function (xmlRoot) {
		var meta = firstChild(xmlRoot, 'Meta');
		var recycleBinUuid = meta ? textOf(firstChild(meta, 'RecycleBinUUID')) : '';
		var templatesUuid = meta ? textOf(firstChild(meta, 'EntryTemplatesGroup')) : '';
		if (templatesUuid === NULL_UUID) {
			templatesUuid = '';
		}

		var pool = await collectPooledBinaries(xmlRoot);
		var cache = {};
		var credentials = [];
		var root = firstChild(xmlRoot, 'Root');
		var topGroups = root ? directChildren(root, 'Group') : directChildren(xmlRoot, 'Group');
		for (var i = 0; i < topGroups.length; i++) {
			await walkGroups(topGroups[i], [], true, recycleBinUuid, templatesUuid, pool, cache, credentials);
		}
		return credentials;
	};

	const parsePwList = function (xmlRoot) {
		var credentials = [];
		var entries = xmlRoot.getElementsByTagName('pwentry');
		for (var i = 0; i < entries.length; i++) {
			var entry = entries[i];
			var credential = PassmanImporter.newCredential();
			credential.label = textOf(firstChild(entry, 'title')) || null;
			credential.username = textOf(firstChild(entry, 'username')) || null;
			credential.password = textOf(firstChild(entry, 'password'), false) || null;
			credential.url = textOf(firstChild(entry, 'url')) || null;
			credential.description = textOf(firstChild(entry, 'notes'), false) || textOf(firstChild(entry, 'comment'), false) || null;
			var group = textOf(firstChild(entry, 'group'));
			if (group) {
				var parts = group.split(/[\\/]+/);
				for (var g = 0; g < parts.length; g++) {
					addTag(credential.tags, parts[g]);
				}
			}
			credential.created = parseKeePassTime(textOf(firstChild(entry, 'creationtime')));
			credential.changed = parseKeePassTime(textOf(firstChild(entry, 'lastmodtime')));
			var expire = textOf(firstChild(entry, 'expiretime')) || textOf(firstChild(entry, 'expire'));
			if (expire && expire.toLowerCase() !== 'never' && expire.indexOf('2999-') !== 0) {
				var expiry = parseKeePassTime(expire);
				if (expiry) {
					credential.expire_time = expiry * 1000;
				}
			}
			if (credential.label) {
				credentials.push(credential);
			}
		}
		return credentials;
	};

	const parseKeePassXDatabase = function (xmlRoot) {
		var credentials = [];
		var walk = function (groupEl, parentPath) {
			var name = textOf(firstChild(groupEl, 'title')) || textOf(firstChild(groupEl, 'name'));
			if (name && name.toLowerCase() === 'recycle bin') {
				return;
			}
			var path = parentPath.slice();
			if (name) {
				path.push(name);
			}

			var entries = directChildren(groupEl, 'entry');
			for (var i = 0; i < entries.length; i++) {
				var entry = entries[i];
				var credential = PassmanImporter.newCredential();
				credential.label = textOf(firstChild(entry, 'title')) || null;
				credential.username = textOf(firstChild(entry, 'username')) || null;
				credential.password = textOf(firstChild(entry, 'password'), false) || null;
				credential.url = textOf(firstChild(entry, 'url')) || null;
				credential.description = textOf(firstChild(entry, 'comment'), false) || textOf(firstChild(entry, 'notes'), false) || null;
				for (var t = 0; t < path.length; t++) {
					addTag(credential.tags, path[t]);
				}
				credential.created = parseKeePassTime(textOf(firstChild(entry, 'creation')));
				credential.changed = parseKeePassTime(textOf(firstChild(entry, 'lastmod')));
				var expire = textOf(firstChild(entry, 'expire'));
				if (expire && expire.toLowerCase() !== 'never' && expire.indexOf('2999-') !== 0) {
					var expiry = parseKeePassTime(expire);
					if (expiry) {
						credential.expire_time = expiry * 1000;
					}
				}
				if (credential.label) {
					credentials.push(credential);
				}
			}

			var groups = directChildren(groupEl, 'group');
			for (var g = 0; g < groups.length; g++) {
				walk(groups[g], path);
			}
		};

		var rootGroups = directChildren(xmlRoot, 'group');
		for (var r = 0; r < rootGroups.length; r++) {
			walk(rootGroups[r], []);
		}
		return credentials;
	};

	PassmanImporter.keepassXml.readFile = function (file_data) {
		/** global: C_Promise */
		return new C_Promise(async function () {
			var self = this;
			try {
				var parser = new DOMParser();
				var xml = parser.parseFromString(file_data, 'application/xml');
				var parserError = xml.querySelector ? xml.querySelector('parsererror') : null;
				if (parserError) {
					throw new Error(textOf(parserError) || 'Invalid XML');
				}

				var root = xml.documentElement;
				var kind = tagName(root);
				var credentials = [];
				if (kind === 'keepassfile') {
					credentials = await parseKeePassFile(root);
				} else if (kind === 'pwlist') {
					credentials = parsePwList(root);
				} else if (kind === 'database') {
					credentials = parseKeePassXDatabase(root);
				} else {
					throw new Error('Unsupported KeePass XML root: ' + (root && root.tagName));
				}

				self.call_progress({
					percent: 100,
					loaded: credentials.length,
					total: credentials.length
				});
				self.call_then(credentials);
			} catch (e) {
				console.error('KeePass XML import failed', e);
				self.call_error(e);
			}
		});
	};
})(window, $, PassmanImporter);
