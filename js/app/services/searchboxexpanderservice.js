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
			var native_search = document.getElementById("searchbox");


			var translations ={};

			$translate.onReady(function() {
				for (var key in defaults) {
					translations[key]=$translate.instant('search.settings.input.'+key);
				}

				var title=$translate.instant('search.settings.title');
				var defaults_button=$translate.instant('search.settings.defaults_button');
				buildPopup(title, defaults_button);
			});


			function getSearchFieldArray(){
				var fields=[];
				for (var key in defaults) {
					if(defaults[key]){
						fields.push(key);
					}
				}
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
						rootScope.$broadcast('nc_searchbox', native_search.value, getSearchFieldArray());
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
				//parent.classList.add("hidden");

				var node = document.createElement("div");
				node.classList.add("icon-category-tools");
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
					//$('#searchbox-settings').removeClass("hidden");

				});

				$('#searchbox').on("blur", function (evt) {
					if (!native_search.value) {
						//$('#searchbox-settings').addClass("hidden");
					}
				});
			}

			function openPopup() {
				if (native_search === null) {
					return;
				}

				$(function () {
					$("#dialog").dialog({
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

				var dialogdiv = document.createElement("div");
				dialogdiv.id = "dialog";
				dialogdiv.title = title;
				dialogdiv.classList.add("hidden");
				native_search.after(dialogdiv);

				for (var key in defaults) {
					var div_inner=document.createElement("div");

					var input = document.createElement("input");
					input.id=key+"_input";
					input.type="checkbox";
					if(defaults[key]){
						input.checked="true";
					}
					input.innerText=key;

					var label = document.createElement("label");
					label.htmlFor=key+"_input";
					label.innerHTML=translations[key];

					div_inner.appendChild(input);
					div_inner.appendChild(label);
					dialogdiv.appendChild(div_inner);



				}

			}


			function addListenerToDefault(rootScope, scope) {
				if (native_search === null) {
					return;
				}
			}

			return {
				expandSearch: function ($rootScope, $scope, translation) {

					buildDefaultFix($rootScope, $scope);
					buildCog();
					addListenerToCog();
					buildPopup(translation);



					$('#searchbox-settings-icon').on("click", function (evt) {
						console.log("click?");
						openPopup();
					});


				},
			};
		}]);
}());