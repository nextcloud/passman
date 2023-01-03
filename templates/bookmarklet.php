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
script('passman', 'app/controllers/edit_credential');
script('passman', 'app/controllers/bookmarklet');
script('passman', 'app/filters/range');
script('passman', 'app/filters/propsfilter');
script('passman', 'app/filters/byte');
script('passman', 'app/filters/tagfilter');
script('passman', 'app/filters/escapeHTML');
script('passman', 'app/filters/as');
script('passman', 'app/filters/toHHMMSS');
script('passman', 'app/services/vaultservice');
script('passman', 'app/services/credentialservice');
script('passman', 'app/services/settingsservice');
script('passman', 'app/services/fileservice');
script('passman', 'app/services/encryptservice');
script('passman', 'app/services/tagservice');
script('passman', 'app/services/notificationservice');
script('passman', 'app/services/shareservice');
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
/*build-js-end*/

/*
 * Styles
 */
/*build-css-start*/
style('passman', 'vendor/ng-password-meter/ng-password-meter');
style('passman', 'vendor/bootstrap/bootstrap.min');
style('passman', 'vendor/bootstrap/bootstrap-theme.min');
style('passman', 'vendor/font-awesome/font-awesome.min');
style('passman', 'vendor/angular-xeditable/xeditable.min');
style('passman', 'vendor/ng-tags-input/ng-tags-input.min');
style('passman', 'vendor/angularjs-datetime-picker/angularjs-datetime-picker');
style('passman', 'app');
/*build-css-end*/

style('passman', 'bookmarklet');

?>

<div id="app" ng-app="passmanApp" ng-controller="BookmarkletCtrl">
	<div class="warning_bar" ng-if="using_http && http_warning_hidden == false">
		{{ 'http.warning' | translate }}
		<i class="fa fa-times fa-2x" alt="Close"
		   ng-click="setHttpWarning(true);"></i>
	</div>
	<div id="app-content">
		<div id="app-content-wrapper" ng-if="active_vault === false">
				<div ng-include="'views/vaults.html'"></div>
		</div>
		<div id="app-content-wrapper" ng-if="active_vault !== false">
            <div id="content">
                <div id="passman-controls">
                    <div class="breadcrumb">
                        <div class="breadcrumb">
                            <div class="crumb svg ui-droppable" data-dir="/">
                                <a><i class="fa fa-home"></i></a>
                            </div>
                            <div class="crumb svg" data-dir="/Test">
                                <a>{{ active_vault.name }}</a>
                            </div>
                            <div class="crumb svg last" data-dir="/Test">
                                <a ng-if="storedCredential.credential_id">{{ 'edit.credential' | translate }}
                                    "{{ storedCredential.label }}"</a>
                                <a ng-if="!storedCredential.credential_id">{{ 'create.credential' | translate }}</a>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="app-sidebar-tabs">
                    <nav class="app-sidebar-tabs__nav">
                        <ul>
                            <li ng-repeat="tab in tabs track by $index" class="app-sidebar-tabs__tab"
                                ng-class="isActiveTab(tab)? 'active' : 'inactive'"
                                ng-click="onClickTab(tab)">{{ tab.title }}
                            </li>
                        </ul>
                    </nav>

                    <div class="tab_container edit_credential">
                        <div ng-include="currentTab.url"></div>
                        <button ng-click="saveCredential()">{{ 'save' | translate }}</button>
                        <button ng-click="cancel()">{{ 'cancel' | translate }}</button>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>
