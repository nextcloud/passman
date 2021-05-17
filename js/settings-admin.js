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


	var settings = new Settings(OC.generateUrl('apps/passman/api/v2/settings'));
	settings.load();
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

	var accountMover = {
		'source_account': '',
		'destination_account': ''
	};
	$('.account_mover_selector').select2({
		ajax: {
			url: OC.generateUrl('apps/passman/admin/search'),
			dataType: 'json',
			delay: 50,
			data: function (param) {
				return {
					term: param
				};
			},
			results: function (data) {
				var res = [];
				for (var i = 0; i < data.length; i++) {
					res.push({
						id: i,
						text: data[i].value
					});
				}
				return {
					results: res
				};
			},
			cache: true
		},
		placeholder: 'Search for a user',
		minimumInputLength: 1
	});

	$('#move_credentials').click(function () {
		var self = this;
		accountMover.source_account = $('#s2id_source_account a .select2-chosen').html();
		accountMover.destination_account = $('#s2id_destination_account a .select2-chosen').html();
		$('#moveStatus').hide();
		$(self).attr('disabled', 'disabled');
		$(self).html('<i class="fa fa-spinner fa-spin"></i> ' + OC.L10N.translate('passman', 'Moving') + '...');
		if (accountMover.source_account && accountMover.destination_account) {
			$.post(OC.generateUrl('apps/passman/admin/move'), accountMover, function (data) {
				$(self).removeAttr('disabled');
				$(self).html('Move');
				if (data.success) {
					$('#moveStatusSucceeded').fadeIn();
					setTimeout(function () {
						$('#moveStatusSucceeded').fadeOut();
					}, 3500);
				} else {
					$('#moveStatusFailed').fadeIn();
					setTimeout(function () {
						$('#moveStatusFailed').fadeOut();
					}, 3500);
				}
			});
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
		$.post(OC.generateUrl('apps/passman/admin/accept-delete-request'), req, function () {
			$(el).parent().parent().remove();
		});
	}

	function ignoreDeleteRequest (el, req) {
		$.ajax({
			url: OC.generateUrl('apps/passman/admin/request-deletion/' + req.vault_guid),
			type: 'DELETE',
			success: function () {
				$(el).parent().parent().remove();
			}
		});
	}

	$.get(OC.generateUrl('apps/passman/admin/delete-requests'), function (requests) {
		var table = $('#requests-table tbody');
		$.each(requests, function (k, request) {
			var accept = $('<span class="link">[Accept]&nbsp;</span>');
			accept.click(function () {
				var _self = this;
				acceptDeleteRequest(_self, request);
			});

			var ignore = $('<span class="link">[Ignore]</span>');
			ignore.click(function () {
				var _self = this;
				ignoreDeleteRequest(_self, request);
			});

			var cols = $('<td>' + request.id + '</td><td>' + request.displayName + '</td><td>' + request.reason + '</td><td>' + format_date(request.created * 1000 )+ '</td>');
			var actions = $('<td></td>').append(accept).append(ignore);
			table.append($('<tr></tr>').append(cols).append(actions));
		});
	});

	$('#passman-tabs').tabs();
});
