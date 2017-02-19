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
				url: this._baseUrl + '/' + key + '/' + value +'/admin1/admin2',
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
			if(this._settings.hasOwnProperty(key)){
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

	$('#passman_sharing_enabled').change(function () {
		settings.setAdminKey('user_sharing_enabled', ($(this).is(":checked")) ? 1 : 0);
	});

	$('#passman_link_sharing_enabled').change(function () {
		settings.setAdminKey('link_sharing_enabled', ($(this).is(":checked")) ? 1 : 0);
	});
	$('#vault_key_strength').change(function () {
		settings.setAdminKey('vault_key_strength', $(this).val());
	});

	if($('form[name="passman_settings"]').length === 2){
		$('form[name="passman_settings"]')[1].remove();
	}

	var accountMover = {
		'source_account': '',
		'destination_account': ''
	};
	$( ".username-autocomplete" ).autocomplete({
		source: OC.generateUrl('apps/passman/admin/search'),
		minLength: 1,
		select: function( event, ui ) {
			accountMover[$(this).attr('id')] = ui.item.value;
		}
	});

	$('#move_credentials').click(function () {
		var self = this;
		$('#moveStatus').hide();
		$(self).attr('disabled', 'disabled');
		$(self).html('<i class="fa fa-spinner fa-spin"></i> Moving...');
		if(accountMover.source_account && accountMover.destination_account){
			$.post(OC.generateUrl('apps/passman/admin/move'), accountMover, function (data) {
				if(data.success){
					$(self).removeAttr('disabled');
					$(self).html('Move');
					$('#moveStatus').fadeIn();
					setTimeout(function () {
						$('#moveStatus').fadeOut();
					}, 3500)
				}
			});
		}
	});

	$('#passman-tabs').tabs();
});
