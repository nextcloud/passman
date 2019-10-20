/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2019, Felix Nüsse (felix.nuesse@t-online.de)
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

(function () {
	'use strict';
	/**
	 * @ngdoc service
	 * @name passmanApp.SearchboxexpanderService
	 * @description
	 * # SearchboxexpanderService
	 * Service in the passmanApp.
	 */
	angular.module('passmanApp')
		.service('SearchboxexpanderService', ['SettingsService', '$translate', function (SettingsService, $translate) {

			var defaults = {'label':true, 'username':true, 'email':true, 'custom_fields':true, 'password':false, 'description':false, 'url':false};
			var searchfields = {'label':true, 'username':true, 'email':true, 'custom_fields':true, 'password':false, 'description':false, 'url':false};
			var native_search = document.getElementById("searchbox");

			var title="Loading...";
			var defaults_button="Loading...";
			var translations ={};

			$translate.onReady(function() {
				for (var key in defaults) {
					translations[key]=$translate.instant('search.settings.input.'+key);
				}

				title=$translate.instant('search.settings.title');
				defaults_button=$translate.instant('search.settings.defaults_button');
			});

			function getSearchFieldArraySettings(){

				var res = SettingsService.getSetting('searchbox_settings');

				if(typeof(res) !== "undefined" && res !== null && res!== ""){
					searchfields = JSON.parse(res);
				}else{
					searchfields=defaults;
				}


			}

			function getSearchFieldArrayForFiltering(){
				var fields=[];
				for (var key in searchfields) {
					if(searchfields[key]){
						fields.push(key);
					}
				}
				return fields;
			}

			//searchboxfix
			function buildDefaultFix(rootScope, scope) {
				if (native_search === null) {
					return;
				}
				native_search.nextElementSibling.addEventListener('click', function (e) {
					scope.$apply(function () {
						rootScope.$broadcast('nc_searchbox', "");
					});
				});

				native_search.classList.remove('hidden');
				native_search.addEventListener('keypress', function (e) {
					if (e.keyCode === 13) {
						e.preventDefault();
					}
				});

				native_search.addEventListener('keyup', function (e) {
					scope.$apply(function () {
						rootScope.$broadcast('nc_searchbox', native_search.value, getSearchFieldArrayForFiltering());
					});
				});

			}

			function buildCog() {
				if (native_search === null) {
					return;
				}

				var parent = document.createElement("div");
				parent.classList.add("notifications");
				parent.id = "searchbox-settings";
				parent.classList.add("hidden");

				var node = document.createElement("div");
				node.classList.add("icon-settings-white");
				node.classList.add("searchbox-settings");
				node.id = "searchbox-settings-icon";

				parent.appendChild(node);
				native_search.after(parent);
			}

			function addListenerToCog() {
				if (native_search === null) {
					return;
				}

				$('#searchbox').on("focus", function (evt) {
					$('#searchbox-settings').removeClass("hidden");

				});

				$('#searchbox').on("blur", function (evt) {
					if (!native_search.value) {
						setTimeout(function() {
								$('#searchbox-settings').addClass("hidden");
							}, 150);
					}
				});
			}

			function openPopup() {
				if (native_search === null) {
					return;
				}

				buildPopup(title, defaults_button);

				$(function () {
					$("#dialog-searchboxsettings").dialog({
						width: 280,
						height: 280,
						dialogClass: 'custom-search-dialog',
						close: function() {
							$(this).dialog('destroy');
						}
					}).removeClass('ui-corner-all');

				});
			}

			function buildPopup(title) {

				if ( $("#dialog-searchboxsettings").length ) {
					$( "#dialog-searchboxsettings" ).remove();
				}

				var dialogdiv = document.createElement("div");
				dialogdiv.id = "dialog-searchboxsettings";
				dialogdiv.title = title;
				dialogdiv.classList.add("hidden");

				native_search.after(dialogdiv);

				getSearchFieldArraySettings();
				for (var key in searchfields) {

					var div_inner=document.createElement("div");
					div_inner.id=key+"_div";

					var input = document.createElement("input");
					input.id=key+"_input";
					input.classList.add("searchbox_settings_input");
					input.setAttribute('key', key);
					input.type="checkbox";
					if(searchfields[key]){
						input.checked="true";
					}
					input.innerText=key;

					var label = document.createElement("label");
					label.classList.add("searchbox_settings_label");
					//label.htmlFor=key+"_input";
					label.innerHTML=translations[key];
					label.setAttribute('key', key);

					div_inner.appendChild(input);
					div_inner.appendChild(label);

					dialogdiv.appendChild(div_inner);



				}
				attachListener();

			}

			function attachListener(){

				$('.searchbox_settings_input').on("change", function(evt) {

					var key = $(this).attr('key');
					searchfields[key]=$("#"+key+"_input").prop('checked');
					var string = JSON.stringify(searchfields);
					SettingsService.setSetting('searchbox_settings', string);

				});

				$('.searchbox_settings_label').on("click", function(evt) {

					return;
					/*var key = $(this).attr("key");

					var checkBoxes = $("#"+key+"_input");
					checkBoxes.prop("checked", !checkBoxes.prop("checked"));

					//todo add functionality here
*/
				});
			}


			return {
				expandSearch: function ($rootScope, $scope, translation) {

					getSearchFieldArraySettings();
					buildDefaultFix($rootScope, $scope);
					buildCog();
					addListenerToCog();

					$('#searchbox-settings-icon').on("click", function (evt) {
						openPopup();
					});

				},
			};
		}]);
}());