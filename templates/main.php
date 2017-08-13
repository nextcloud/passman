<?php
/*
 * Javascripts
 */
/*build-js-start*/
script('passman', 'vendor/angular/angular.min');
script('passman', 'vendor/angular-animate/angular-animate.min');
script('passman', 'vendor/angular-cookies/angular-cookies.min');
script('passman', 'vendor/angular-resource/angular-resource.min');
script('passman', 'vendor/angular-route/angular-route.min');
script('passman', 'vendor/angular-sanitize/angular-sanitize.min');
script('passman', 'vendor/angular-touch/angular-touch.min');
script('passman', 'vendor/angular-local-storage/angular-local-storage.min');
script('passman', 'vendor/angular-off-click/angular-off-click.min');
script('passman', 'vendor/angularjs-datetime-picker/angularjs-datetime-picker.min');
script('passman', 'vendor/angular-translate/angular-translate.min');
script('passman', 'vendor/angular-translate/angular-translate-loader-url.min');
script('passman', 'vendor/ng-password-meter/ng-password-meter');
script('passman', 'vendor/sjcl/sjcl');
script('passman', 'vendor/zxcvbn/zxcvbn');
script('passman', 'vendor/ng-clipboard/clipboard.min');
script('passman', 'vendor/ng-clipboard/ngclipboard');
script('passman', 'vendor/ng-tags-input/ng-tags-input.min');
script('passman', 'vendor/angular-xeditable/xeditable.min');
script('passman', 'vendor/sha/sha');
script('passman', 'vendor/llqrcode/llqrcode');
script('passman', 'vendor/forge.0.6.9.min');
script('passman', 'vendor/download');
script('passman', 'vendor/ui-sortable/sortable');
script('passman', 'vendor/papa-parse/papaparse.min');
script('passman', 'lib/promise');
script('passman', 'lib/crypto_wrap');


script('passman', 'app/app');
script('passman', 'templates');
script('passman', 'app/controllers/main');
script('passman', 'app/controllers/menu');
script('passman', 'app/controllers/vault');
script('passman', 'app/controllers/credential');
script('passman', 'app/controllers/edit_credential');
script('passman', 'app/controllers/share');
script('passman', 'app/controllers/share_settings');
script('passman', 'app/controllers/revision');
script('passman', 'app/controllers/settings');
script('passman', 'app/controllers/import');
script('passman', 'app/controllers/export');
script('passman', 'app/controllers/generic-csv-importer');
script('passman', 'app/controllers/vaultreqdeletion');
script('passman', 'app/filters/range');
script('passman', 'app/filters/propsfilter');
script('passman', 'app/filters/byte');
script('passman', 'app/filters/tagfilter');
script('passman', 'app/filters/as');
script('passman', 'app/filters/credentialsearch');
script('passman', 'app/filters/toHHMMSS');
script('passman', 'app/services/cacheservice');
script('passman', 'app/services/vaultservice');
script('passman', 'app/services/credentialservice');
script('passman', 'app/services/settingsservice');
script('passman', 'app/services/fileservice');
script('passman', 'app/services/encryptservice');
script('passman', 'app/services/iconservice');
script('passman', 'app/services/tagservice');
script('passman', 'app/services/notificationservice');
script('passman', 'app/services/shareservice');
script('passman', 'app/factory/sharingacl');
script('passman', 'app/directives/passwordgen');
script('passman', 'app/directives/fileselect');
script('passman', 'app/directives/progressbar');
script('passman', 'app/directives/otp');
script('passman', 'app/directives/qrreader');
script('passman', 'app/directives/tooltip');
script('passman', 'app/directives/use-theme');
script('passman', 'app/directives/credentialfield');
script('passman', 'app/directives/ngenter');
script('passman', 'app/directives/autoscroll');
script('passman', 'app/directives/clickselect');
script('passman', 'app/directives/colorfromstring');
script('passman', 'app/directives/credentialcounter');
script('passman', 'app/directives/clearbutton2');
script('passman', 'app/directives/credentialtemplate');
script('passman', 'app/directives/clickdisable');
script('passman', 'app/directives/icon');
script('passman', 'app/directives/iconpicker');
script('passman', 'importers/import-main');
script('passman', 'importers/importer-keepasscsv');
script('passman', 'importers/importer-lastpasscsv');
script('passman', 'importers/importer-dashlanecsv');
script('passman', 'importers/importer-zohocsv');
script('passman', 'importers/importer-passmanjson');
script('passman', 'importers/importer-ocpasswords');
script('passman', 'importers/importer-clipperz');
script('passman', 'importers/importer-teampass');
script('passman', 'importers/importer-enpass');
script('passman', 'importers/importer-passpackcsv');
script('passman', 'importers/importer-randomdata');
script('passman', 'importers/importer-padlock');
script('passman', 'exporters/exporter-main');
script('passman', 'exporters/exporter-csv');
script('passman', 'exporters/exporter-json');
/*build-js-end*/


/*
 * Styles
 */
/*build-css-start*/
style('passman', 'vendor/ng-password-meter/ng-password-meter');
style('passman', 'vendor/bootstrap/bootstrap.min');

style('passman', 'vendor/font-awesome/font-awesome.min');
style('passman', 'vendor/angular-xeditable/xeditable.min');
style('passman', 'vendor/ng-tags-input/ng-tags-input.min');
style('passman', 'vendor/angularjs-datetime-picker/angularjs-datetime-picker');
style('passman', 'app');
/*build-css-end*/
?>

<div id="app" ng-app="passmanApp" ng-controller="MainCtrl">
	<div id="logoutTimer"> </div>
	<div class="warning_bar" ng-if="using_http && http_warning_hidden == false">
		{{ 'http.warning' | translate }}
		<i class="fa fa-times fa-2x" alt="Close" ng-click="setHttpWarning(true);"></i>
	</div>

	<div id="app-navigation" ng-show="selectedVault" ng-controller="MenuCtrl">
		<ul>
			<li class="taginput">
				<a class="taginput">
					<tags-input ng-model="selectedTags" replace-spaces-with-dashes="false">
						<auto-complete source="getTags($query)" min-length="0"></auto-complete>
					</tags-input>
				</a>
			</li>

			<li ng-repeat="tag in available_tags | orderBy:'text'" ng-if="selectedTags.indexOf(tag) == -1">
				<a ng-click="tagClicked(tag)">{{tag.text}}</a>
			</li>
			<li data-id="trashbin" class="nav-trashbin">
				<a ng-click="toggleDeleteTime()"
				   ng-class="{'active': delete_time > 0}">
					<i href="#" class="fa fa-trash"></i>
					{{ 'deleted.credentials' | translate }}
				</a>
			</li>
		</ul>

		<div id="app-settings" ng-init="settingsShown = false;">
			<div id="app-settings-header">
				<button class="settings-button"
						ng-click="settingsShown = !settingsShown"
				>{{ 'settings' | translate }}
				</button>
			</div>
			<div id="app-settings-content" ng-show="settingsShown">
				<!-- Your settings in here -->
				<div class="settings-container">
					<div><a class="link" ng-href="#/vault/{{active_vault.guid}}/settings">{{ 'settings' | translate }}</a></div>
					<div><span class="link" ng-click="logout()">{{'logout' | translate }}</span></div>
					<div><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6YS8F97PETVU2" target="_blank" class="link">{{ 'donate' | translate }}</a></div>
					<div ng-show="session_time_left">
						<small>{{'session.time.left' | translate:translationData}}</small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<div id="content" ng-view="">

			</div>
		</div>
	</div>
</div>
