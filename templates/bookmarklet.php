<?php

include_once '_config.php';

/*
 * Javascripts
 */
/*build-js-start*/
script(MyAppTemplateConfig::APP_ID, 'vendor/angular/angular.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-animate/angular-animate.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-cookies/angular-cookies.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-resource/angular-resource.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-route/angular-route.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-sanitize/angular-sanitize.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-touch/angular-touch.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-local-storage/angular-local-storage.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-off-click/angular-off-click.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angularjs-datetime-picker/angularjs-datetime-picker.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-translate/angular-translate.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-translate/angular-translate-loader-url.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/ng-password-meter/ng-password-meter');
script(MyAppTemplateConfig::APP_ID, 'vendor/sjcl/sjcl');
script(MyAppTemplateConfig::APP_ID, 'vendor/zxcvbn/zxcvbn');
script(MyAppTemplateConfig::APP_ID, 'vendor/ng-clipboard/clipboard.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/ng-clipboard/ngclipboard');
script(MyAppTemplateConfig::APP_ID, 'vendor/ng-tags-input/ng-tags-input.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-xeditable/xeditable.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/sha/sha');
script(MyAppTemplateConfig::APP_ID, 'vendor/llqrcode/llqrcode');
script(MyAppTemplateConfig::APP_ID, 'vendor/forge.0.6.9.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/download');
script(MyAppTemplateConfig::APP_ID, 'vendor/ui-sortable/sortable');
script(MyAppTemplateConfig::APP_ID, 'vendor/papa-parse/papaparse.min');
script(MyAppTemplateConfig::APP_ID, 'lib/promise');
script(MyAppTemplateConfig::APP_ID, 'lib/crypto_wrap');


script(MyAppTemplateConfig::APP_ID, 'app/app');
script(MyAppTemplateConfig::APP_ID, 'templates');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/edit_credential');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/bookmarklet');
script(MyAppTemplateConfig::APP_ID, 'app/filters/range');
script(MyAppTemplateConfig::APP_ID, 'app/filters/propsfilter');
script(MyAppTemplateConfig::APP_ID, 'app/filters/byte');
script(MyAppTemplateConfig::APP_ID, 'app/filters/tagfilter');
script(MyAppTemplateConfig::APP_ID, 'app/filters/escapeHTML');
script(MyAppTemplateConfig::APP_ID, 'app/filters/as');
script(MyAppTemplateConfig::APP_ID, 'app/filters/toHHMMSS');
script(MyAppTemplateConfig::APP_ID, 'app/services/vaultservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/credentialservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/settingsservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/fileservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/encryptservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/tagservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/notificationservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/shareservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/urlservice');
script(MyAppTemplateConfig::APP_ID, 'app/directives/passwordgen');
script(MyAppTemplateConfig::APP_ID, 'app/directives/fileselect');
script(MyAppTemplateConfig::APP_ID, 'app/directives/progressbar');
script(MyAppTemplateConfig::APP_ID, 'app/directives/otp');
script(MyAppTemplateConfig::APP_ID, 'app/directives/qrreader');
script(MyAppTemplateConfig::APP_ID, 'app/directives/tooltip');
script(MyAppTemplateConfig::APP_ID, 'app/directives/use-theme');
script(MyAppTemplateConfig::APP_ID, 'app/directives/credentialfield');
script(MyAppTemplateConfig::APP_ID, 'app/directives/ngenter');
script(MyAppTemplateConfig::APP_ID, 'app/directives/autoscroll');
script(MyAppTemplateConfig::APP_ID, 'app/directives/clickselect');
script(MyAppTemplateConfig::APP_ID, 'app/directives/colorfromstring');
/*build-js-end*/

/*
 * Styles
 */
/*build-css-start*/
style(MyAppTemplateConfig::APP_ID, 'vendor/ng-password-meter/ng-password-meter');
style(MyAppTemplateConfig::APP_ID, 'vendor/bootstrap/bootstrap.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/bootstrap/bootstrap-theme.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/font-awesome/font-awesome.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/angular-xeditable/xeditable.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/ng-tags-input/ng-tags-input.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/angularjs-datetime-picker/angularjs-datetime-picker');
style(MyAppTemplateConfig::APP_ID, 'app');
/*build-css-end*/

style(MyAppTemplateConfig::APP_ID, 'bookmarklet');

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
