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
script(MyAppTemplateConfig::APP_ID, 'lib/otpauth.umd');


script(MyAppTemplateConfig::APP_ID, 'app/app');
script(MyAppTemplateConfig::APP_ID, 'templates');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/main');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/menu');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/vault');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/credential');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/edit_credential');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/share');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/share_settings');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/revision');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/settings');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/import');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/export');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/generic-csv-importer');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/vaultreqdeletion');
script(MyAppTemplateConfig::APP_ID, 'app/filters/range');
script(MyAppTemplateConfig::APP_ID, 'app/filters/propsfilter');
script(MyAppTemplateConfig::APP_ID, 'app/filters/byte');
script(MyAppTemplateConfig::APP_ID, 'app/filters/tagfilter');
script(MyAppTemplateConfig::APP_ID, 'app/filters/escapeHTML');
script(MyAppTemplateConfig::APP_ID, 'app/filters/as');
script(MyAppTemplateConfig::APP_ID, 'app/filters/credentialsearch');
script(MyAppTemplateConfig::APP_ID, 'app/filters/toHHMMSS');
script(MyAppTemplateConfig::APP_ID, 'app/services/cacheservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/vaultservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/credentialservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/settingsservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/fileservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/encryptservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/iconservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/tagservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/notificationservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/shareservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/searchboxexpanderservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/urlservice');
script(MyAppTemplateConfig::APP_ID, 'app/factory/sharingacl');
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
script(MyAppTemplateConfig::APP_ID, 'app/directives/credentialcounter');
script(MyAppTemplateConfig::APP_ID, 'app/directives/clearbutton2');
script(MyAppTemplateConfig::APP_ID, 'app/directives/credentialtemplate');
script(MyAppTemplateConfig::APP_ID, 'app/directives/clickdisable');
script(MyAppTemplateConfig::APP_ID, 'app/directives/icon');
script(MyAppTemplateConfig::APP_ID, 'app/directives/iconpicker');
script(MyAppTemplateConfig::APP_ID, 'importers/import-main');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-keepasscsv');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-lastpasscsv');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-dashlanecsv');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-zohocsv');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-passmanjson');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-ocpasswords');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-clipperz');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-teampass');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-enpass');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-passpackcsv');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-randomdata');
script(MyAppTemplateConfig::APP_ID, 'importers/importer-padlock');
script(MyAppTemplateConfig::APP_ID, 'exporters/exporter-main');
script(MyAppTemplateConfig::APP_ID, 'exporters/exporter-csv');
script(MyAppTemplateConfig::APP_ID, 'exporters/exporter-json');
/*build-js-end*/

/*
 * Styles
 */
/*build-css-start*/
style(MyAppTemplateConfig::APP_ID, 'vendor/ng-password-meter/ng-password-meter');
style(MyAppTemplateConfig::APP_ID, 'vendor/bootstrap/bootstrap.min');

style(MyAppTemplateConfig::APP_ID, 'vendor/font-awesome/font-awesome.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/angular-xeditable/xeditable.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/ng-tags-input/ng-tags-input.min');
style(MyAppTemplateConfig::APP_ID, 'vendor/angularjs-datetime-picker/angularjs-datetime-picker');
style(MyAppTemplateConfig::APP_ID, 'app');
/*build-css-end*/
?>

<div id="app" ng-app="passmanApp" ng-controller="MainCtrl" style="display: flex;">
    <div id="logoutTimer"></div>
    <div id="warning_bar" class="warning_bar template-hidden" ng-if="using_http && http_warning_hidden == false" ng-init="removeHiddenStyles()">
        {{ 'http.warning' | translate }}
        <i class="fa fa-times fa-2x" alt="Close" ng-click="setHttpWarning(true);"></i>
    </div>

    <div id="app-navigation" class="template-hidden" ng-if="selectedVault" ng-controller="MenuCtrl" ng-init="removeHiddenStyles()">
        <ul class="with-icon" ng-class="{ 'hidden-list': !legacyNavbar }" >

            <li>
                <a ng-class="{selected: clickedNavigationItem=='all'}" class="icon-toggle svg" ng-click="filterCredentialBySpecial('all')">{{ 'navigation.show.all' | translate }}</a>
            </li>
            <li class="collapsible" ng-class="tagCollapsibleState()">
                <button class="collapse" ng-click="tagCollapsibleClicked()"></button>
                <a href="" class="icon-tag" ng-click="tagCollapsibleClicked()">{{ 'navigation.tags' | translate }}</a>
                <ul>
                    <li class="taginput">
                        <a class="icon-search taginput">
                            <form ng-submit="tagClickedString(taginput); clearForm();">
                                <input id="tagsearch" list="tags" ng-model="taginput" placeholder="{{ 'navigation.tags.search' | translate }}" />
                                <datalist id="tags">
                                    <option ng-repeat="qtag in getTags($query)" value="{{qtag.text}}">
                                </datalist>
                            </form>
                        </a>
                    </li>

                    <li ng-repeat="tag in available_tags | orderBy:'text'">
                        <div ng-if="tagSelected(tag)"
                             class="app-navigation-entry-bullet app-navigation-entry-bullet-color"></div>
                        <a class="icon-tag svg" ng-click="tagClicked(tag)">{{tag.text}}</a>
                    </li>
                </ul>
            </li>
            <li>
                <div class="app-navigation-entry-bullet-with-hover bullet-color-red"></div>
                <a ng-class="{selected: clickedNavigationItem=='compromised'}" ng-click="filterCredentialBySpecial('compromised')">{{ 'navigation.compromised' | translate }}</a>
            </li>
            <li>
                <div class="app-navigation-entry-bullet-with-hover bullet-color-red"></div>
                <a ng-class="{selected: clickedNavigationItem=='strength_low'}" ng-click="filterCredentialBySpecial('strength_low')">{{ 'navigation.strength.bad' | translate }}</a>
            </li>
            <li>
                <div class="app-navigation-entry-bullet-with-hover bullet-color-yellow"></div>
                <a ng-class="{selected: clickedNavigationItem=='strength_medium'}" ng-click="filterCredentialBySpecial('strength_medium')">{{ 'navigation.strength.medium' | translate }}</a>
            </li>
            <li>
                <div class="app-navigation-entry-bullet-with-hover bullet-color-green"></div>
                <a ng-class="{selected: clickedNavigationItem=='strength_good'}" ng-click="filterCredentialBySpecial('strength_good')">{{ 'navigation.strength.good' | translate }}</a>
            </li>
            <li>
                <a ng-class="{selected: clickedNavigationItem=='expired'}" class="icon-expired svg" ng-click="filterCredentialBySpecial('expired')">{{ 'navigation.expired' | translate }}</a>
            </li>
            <li data-id="trashbin" class="nav-trashbin pinned first-pinned">
                <a ng-click="toggleDeleteTime()" ng-class="{'active': delete_time > 0}" class="icon-delete svg">
                    {{ 'deleted.credentials' | translate }}
                </a>
            </li>
        </ul >
        <ul class="with-icon hidden-list" ng-class="{ 'hidden-list': legacyNavbar }">
            <li class="taginput">
                <a class="taginput icon-search">
                    <tags-input id="tags-input-outer" ng-model="selectedTags" replace-spaces-with-dashes="false" ng-init="initPlaceholder()">
                        <auto-complete source="getTags($query)" min-length="0"></auto-complete>
                    </tags-input>
                </a>
            </li>
            <li ng-repeat="tag in available_tags | orderBy:'text'" ng-if="selectedTags.indexOf(tag) == -1">
                <a class="icon-tag svg" ng-click="tagClicked(tag)">{{tag.text}}</a>
            </li>
            <li data-id="trashbin" class="nav-trashbin pinned first-pinned">
                <a ng-click="toggleDeleteTime()" ng-class="{'active': delete_time > 0}" class="icon-delete svg">
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
            <div id="app-settings-content" class="hide-animation" ng-hide="!settingsShown">

                <div class="settings-container-label">
                    <input class="checkbox" id="navbarLegacyMode" type="checkbox" ng-model="legacyNavbar">
                    <label for="navbarLegacyMode">{{'navigation.advanced.checkbox' | translate }}</label>
                </div>

                <div class="settings-container">
                    <a ng-href="#/vault/{{active_vault.guid}}/settings" class="link" ng-click="settingsShown = false;">
                        <button>{{ 'settings' | translate }}</button>
                    </a>
                </div>
                <div class="settings-container">
                    <button ng-click="logout()"><span class="link">{{'logout' | translate }}</span></button>
                </div>
                <div class="donation-container settings-container">
                    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6YS8F97PETVU2"
                       target="_blank" class="link" ng-click="settingsShown = false;">
                        <button class="donation-container">{{ 'donate' | translate }}</button>
                    </a>
                </div>
                <div class="settings-container">
                    <div ng-show="session_time_left">
                        <small>{{'session.time.left' | translate:translationData}}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="app-content" ng-class="{'vaultlist_sidebar_hidden': !selectedVault}">
        <div id="app-content-wrapper">
            <div id="inner-app-content" ng-view="">

            </div>
        </div>
    </div>
</div>
