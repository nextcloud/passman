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

$(document).ready(function () {
	const urlPrefix = 'apps/passman';

	// Token injection logic required for ajax requests with Nextcloud 34+ since they removed their upstream jquery
	function getRequestToken() {
		if (typeof OC !== 'undefined' && OC.requestToken) {
			return OC.requestToken;
		}
		if (typeof oc_requesttoken !== 'undefined' && oc_requesttoken) {
			return oc_requesttoken;
		}
		var head = document.head || document.getElementsByTagName('head')[0];
		return (head && head.dataset.requesttoken) ? head.dataset.requesttoken : '';
	}

	function getAjaxMethod(options) {
		return (options.type || options.method || 'GET').toUpperCase();
	}

	$.ajaxPrefilter(function (options) {
		var token = getRequestToken();
		if (!token) {
			return;
		}

		var method = getAjaxMethod(options);

		// Nextcloud accepts CSRF via the requesttoken header; avoid mutating POST data
		// because jQuery expects urlencoded bodies to be strings before sending.
		options.headers = $.extend({}, options.headers, { requesttoken: token });

		if (method === 'GET' && options.url && options.url.indexOf('requesttoken=') === -1) {
			options.url += (options.url.indexOf('?') === -1 ? '?' : '&')
				+ 'requesttoken=' + encodeURIComponent(token);
		}
	});

	var Settings = function (baseUrl) {
		this._baseUrl = baseUrl;
		this._settings = [];
	};

	Settings.prototype = {
		load: function () {
			var deferred = $.Deferred();
			var self = this;
			$.ajax({
				url: this._baseUrl,
				method: 'GET',
				async: false
			}).done(function (settings) {
				self._settings = settings;
			}).fail(function () {
				deferred.reject();
			});
			return deferred.promise();
		},

		setUserKey: function (key, value) {
			var request = $.ajax({
				url: this._baseUrl + '/' + key + '/' + value,
				method: 'POST'
			});
			request.done(function () {
				$('.msg-passwords').removeClass("msg_error");
				$('.msg-passwords').text('');
			});
			request.fail(function () {
				$('.msg-passwords').addClass("msg_error");
				$('.msg-passwords').text(t('passwords', 'Error while saving field') + ' ' + key + '!');
			});
		},

		setAdminKey: function (key, value) {
			var request = $.ajax({
				url: this._baseUrl + '/' + key + '/' + value + '/admin1/admin2',
				method: 'POST'
			});
			request.done(function () {
				$('.msg-passwords').removeClass("msg_error");
				$('.msg-passwords').text('');
			});
			request.fail(function () {
				$('.msg-passwords').addClass("msg_error");
				$('.msg-passwords').text(t('passwords', 'Error while saving field') + ' ' + key + '!');
			});
		},
		getKey: function (key) {
			if (this._settings.hasOwnProperty(key)) {
				return this._settings[key];
			}
			return false;
		},
		getAll: function () {
			return this._settings;
		}
	};

	function initTabs() {
		var $tabs = $('#passman-tabs');

		function activateTab(tabId) {
			$tabs.find('.tabHeader').removeClass('selected').attr('aria-selected', 'false');
			$tabs.find('.tabHeader[data-tab="' + tabId + '"]').addClass('selected').attr('aria-selected', 'true');
			$tabs.find('.tabsContainer .tab').addClass('hidden');
			$tabs.find('.tabsContainer .tab[data-tab="' + tabId + '"]').removeClass('hidden');
		}

		$tabs.find('.tabHeader').on('click', function (event) {
			event.preventDefault();
			activateTab($(this).data('tab'));
		});
	}

	function initUserSearch(inputId, hiddenId) {
		var $input = $('#' + inputId);
		var $hidden = $('#' + hiddenId);
		var $dropdown = $input.closest('.passman-admin-user-search-wrap').find('.passman-admin-user-dropdown');
		var searchTimeout;

		$input.on('input', function () {
			var term = $(this).val().trim();
			$hidden.val('');

			if (term.length < 1) {
				$dropdown.empty().addClass('hidden');
				return;
			}

			clearTimeout(searchTimeout);
			searchTimeout = setTimeout(function () {
				$.getJSON(OC.generateUrl(urlPrefix + '/admin/search'), { term: term }, function (data) {
					$dropdown.empty();

					if (!data.length) {
						$dropdown.addClass('hidden');
						return;
					}

					$.each(data, function (_, user) {
						var label = user.label || user.value;
						var $item = $('<button type="button" class="passman-admin-user-option"></button>')
							.text(label)
							.attr('data-value', user.value);
						$dropdown.append($('<li></li>').append($item));
					});

					$dropdown.removeClass('hidden');
				});
			}, 200);
		});

		$dropdown.on('click', '.passman-admin-user-option', function () {
			$input.val($(this).text());
			$hidden.val($(this).attr('data-value'));
			$dropdown.empty().addClass('hidden');
		});

		$(document).on('click.passman-admin-user-search', function (event) {
			if (!$(event.target).closest('.passman-admin-user-search-wrap').length) {
				$dropdown.addClass('hidden');
			}
		});
	}

	function showMoveStatus($element) {
		$('#moveStatusSucceeded, #moveStatusFailed').addClass('hidden');
		$element.removeClass('hidden');
		setTimeout(function () {
			$element.addClass('hidden');
		}, 3500);
	}

	var settings = new Settings(OC.generateUrl(urlPrefix + '/api/v2/settings'));
	settings.load();

	initTabs();
	initUserSearch('source_account_input', 'source_account');
	initUserSearch('destination_account_input', 'destination_account');

	// ADMIN SETTINGS

	// fill the boxes
	$('#passman_link_sharing_enabled').prop('checked', (settings.getKey('link_sharing_enabled').toString().toLowerCase() === '1'));
	$('#passman_sharing_enabled').prop('checked', (settings.getKey('user_sharing_enabled').toString().toLowerCase() === '1'));
	$('#passman_check_version').prop('checked', (settings.getKey('check_version').toString().toLowerCase() === '1'));
	$('#passman_https_check').prop('checked', (settings.getKey('https_check').toString().toLowerCase() === '1'));
	$('#passman_disable_contextmenu').prop('checked', (settings.getKey('disable_contextmenu').toString().toLowerCase() === '1'));
	$('#passman_disable_debugger').prop('checked', (settings.getKey('disable_debugger').toString().toLowerCase() === '1'));
	$('#passman_enable_global_search').prop('checked', (settings.getKey('enable_global_search').toString().toLowerCase() === '1'));
	$('#vault_key_strength').val(settings.getKey('vault_key_strength'));


	$('#passman_check_version').change(function () {
		settings.setAdminKey('check_version', ($(this).is(":checked")) ? 1 : 0);
	});

	$('#passman_https_check').change(function () {
		settings.setAdminKey('https_check', ($(this).is(":checked")) ? 1 : 0);
	});

	$('#passman_disable_contextmenu').change(function () {
		settings.setAdminKey('disable_contextmenu', ($(this).is(":checked")) ? 1 : 0);
	});

	$('#passman_disable_debugger').change(function () {
		settings.setAdminKey('disable_debugger', ($(this).is(":checked")) ? 1 : 0);
	});

	$('#passman_enable_global_search').change(function () {
		settings.setAdminKey('enable_global_search', ($(this).is(":checked")) ? 1 : 0);
	});

	$('#passman_sharing_enabled').change(function () {
		settings.setAdminKey('user_sharing_enabled', ($(this).is(":checked")) ? 1 : 0);
	});

	$('#passman_link_sharing_enabled').change(function () {
		settings.setAdminKey('link_sharing_enabled', ($(this).is(":checked")) ? 1 : 0);
	});
	$('#vault_key_strength').change(function () {
		settings.setAdminKey('vault_key_strength', $(this).val());
	});

	if ($('form[name="passman_settings"]').length === 2) {
		$('form[name="passman_settings"]')[1].remove();
	}

	$('#move_credentials').click(function () {
		var self = this;
		var $button = $(self);
		var moveLabel = $button.data('label') || $button.text();
		var accountMover = {
			source_account: $('#source_account').val(),
			destination_account: $('#destination_account').val()
		};

		$('#moveStatusSucceeded, #moveStatusFailed').addClass('hidden');
		$button.prop('disabled', true);
		$button.text(OC.L10N.translate('passman', 'Moving') + '...');

		if (accountMover.source_account && accountMover.destination_account) {
			$.post(OC.generateUrl(urlPrefix + '/admin/move'), accountMover, function (data) {
				$button.prop('disabled', false);
				$button.text(moveLabel);
				if (data.success) {
					showMoveStatus($('#moveStatusSucceeded'));
				} else {
					showMoveStatus($('#moveStatusFailed'));
				}
			});
		} else {
			$button.prop('disabled', false);
			$button.text(moveLabel);
			showMoveStatus($('#moveStatusFailed'));
		}
	});

	function format_date(date) {
		date = new Date(date);
		var month=date.getMonth();
		var year=date.getFullYear();
		var day=date.getDate();
		var hour=date.getHours();
		var minutes=date.getMinutes();
		var seconds=date.getSeconds();

		month=month+1; //javascript date goes from 0 to 11
		if (month<10){
			month="0"+month; //adding the prefix
		}
		if (hour<10){
			hour="0"+hour; //adding the prefix
		}
		if (minutes<10){
			minutes="0"+minutes; //adding the prefix
		}
		if (seconds<10){
			seconds="0"+seconds; //adding the prefix
		}



		return day+"-"+month+"-"+year+" "+hour+":"+minutes+":"+seconds;
	}

	function acceptDeleteRequest (el, req) {
		if (!confirm(OC.L10N.translate('passman', "Are you really sure?\nThis will delete the vault and all credentials in it!"))) {
			return;
		}
		$.post(OC.generateUrl(urlPrefix + '/admin/accept-delete-request'), req, function () {
			$(el).closest('tr').remove();
		});
	}

	function ignoreDeleteRequest (el, req) {
		$.ajax({
			url: OC.generateUrl(urlPrefix + '/admin/request-deletion/' + req.vault_guid),
			type: 'DELETE',
			success: function () {
				$(el).closest('tr').remove();
			}
		});
	}

	$.get(OC.generateUrl(urlPrefix + '/admin/delete-requests'), function (requests) {
		var table = $('#requests-table tbody');
		$.each(requests, function (k, request) {
			var accept = $('<button type="button" class="passman-admin-action passman-admin-action--accept"></button>')
				.text(OC.L10N.translate('passman', 'Accept'));
			accept.click(function () {
				acceptDeleteRequest(this, request);
			});

			var ignore = $('<button type="button" class="passman-admin-action passman-admin-action--ignore"></button>')
				.text(OC.L10N.translate('passman', 'Ignore'));
			ignore.click(function () {
				ignoreDeleteRequest(this, request);
			});

			var cols = $('<td>' + request.id + '</td><td>' + request.displayName + '</td><td>' + request.reason + '</td><td>' + format_date(request.created * 1000 )+ '</td>');
			var actions = $('<td class="passman-admin-table-actions"></td>').append(accept).append(ignore);
			table.append($('<tr></tr>').append(cols).append(actions));
		});
	});
});
