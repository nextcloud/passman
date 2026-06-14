<?php

use OCP\Util;

include_once '_config.php';

/*
 * Javascripts
 */
/*build-js-start*/
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/jquery-3.7.1.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/jquery-ui.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular/angular.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-animate/angular-animate.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-cookies/angular-cookies.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-resource/angular-resource.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-route/angular-route.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-sanitize/angular-sanitize.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-touch/angular-touch.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-local-storage/angular-local-storage.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-off-click/angular-off-click.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angularjs-datetime-picker/angularjs-datetime-picker.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-translate/angular-translate.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-translate/angular-translate-loader-url.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/ng-password-meter/ng-password-meter', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/sjcl/sjcl', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/zxcvbn/zxcvbn', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/ng-clipboard/clipboard.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/ng-clipboard/ngclipboard', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/ng-tags-input/ng-tags-input.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/angular-xeditable/xeditable.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/sha/sha', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/llqrcode/llqrcode', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/forge.0.6.9.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/download', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/ui-sortable/sortable', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/papa-parse/papaparse.min', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'lib/promise', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'lib/crypto_wrap', 'core');


Util::addScript(MyAppTemplateConfig::APP_ID, 'app/app', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'templates', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/controllers/edit_credential', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/controllers/bookmarklet', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/filters/range', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/filters/propsfilter', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/filters/byte', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/filters/tagfilter', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/filters/escapeHTML', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/filters/as', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/filters/toHHMMSS', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/vaultservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/credentialservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/settingsservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/fileservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/encryptservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/tagservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/notificationservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/shareservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/services/urlservice', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/passwordgen', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/fileselect', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/progressbar', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/otp', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/qrreader', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/tooltip', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/use-theme', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/credentialfield', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/ngenter', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/autoscroll', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/clickselect', 'core');
Util::addScript(MyAppTemplateConfig::APP_ID, 'app/directives/colorfromstring', 'core');
/*build-js-end*/

/*
 * Styles
 */
/*build-css-start*/
style(MyAppTemplateConfig::APP_ID, 'vendor/ng-password-meter/ng-password-meter');
style(MyAppTemplateConfig::APP_ID, 'vendor/bootstrap/bootstrap.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/bootstrap/bootstrap-theme.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/jquery-ui.min');
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
