<?php

use OCP\Util;

/*
 * Javascripts
 */
/*build-js-start*/
Util::addScript('passman', 'vendor/angular/angular.min');
Util::addScript('passman', 'vendor/angular-animate/angular-animate.min');
Util::addScript('passman', 'vendor/angular-cookies/angular-cookies.min');
Util::addScript('passman', 'vendor/angular-resource/angular-resource.min');
Util::addScript('passman', 'vendor/angular-route/angular-route.min');
Util::addScript('passman', 'vendor/angular-sanitize/angular-sanitize.min');
Util::addScript('passman', 'vendor/angular-touch/angular-touch.min');
Util::addScript('passman', 'vendor/angular-local-storage/angular-local-storage.min');
Util::addScript('passman', 'vendor/angular-off-click/angular-off-click.min');
Util::addScript('passman', 'vendor/angularjs-datetime-picker/angularjs-datetime-picker.min');
Util::addScript('passman', 'vendor/angular-translate/angular-translate.min');
Util::addScript('passman', 'vendor/angular-translate/angular-translate-loader-url.min');
Util::addScript('passman', 'vendor/ng-password-meter/ng-password-meter');
Util::addScript('passman', 'vendor/sjcl/sjcl');
Util::addScript('passman', 'vendor/zxcvbn/zxcvbn');
Util::addScript('passman', 'vendor/ng-clipboard/clipboard.min');
Util::addScript('passman', 'vendor/ng-clipboard/ngclipboard');
Util::addScript('passman', 'vendor/ng-tags-input/ng-tags-input.min');
Util::addScript('passman', 'vendor/angular-xeditable/xeditable.min');
Util::addScript('passman', 'vendor/sha/sha');
Util::addScript('passman', 'vendor/llqrcode/llqrcode');
Util::addScript('passman', 'vendor/forge.0.6.9.min');
Util::addScript('passman', 'vendor/download');
Util::addScript('passman', 'vendor/ui-sortable/sortable');
Util::addScript('passman', 'vendor/papa-parse/papaparse.min');
Util::addScript('passman', 'lib/promise');
Util::addScript('passman', 'lib/crypto_wrap');
Util::addScript('passman', 'lib/otpauth.umd');


Util::addScript('passman', 'app/app');
Util::addScript('passman', 'templates');
Util::addScript('passman', 'app/controllers/main');
Util::addScript('passman', 'app/controllers/menu');
Util::addScript('passman', 'app/controllers/vault');
Util::addScript('passman', 'app/controllers/credential');
Util::addScript('passman', 'app/controllers/edit_credential');
Util::addScript('passman', 'app/controllers/share');
Util::addScript('passman', 'app/controllers/share_settings');
Util::addScript('passman', 'app/controllers/revision');
Util::addScript('passman', 'app/controllers/settings');
Util::addScript('passman', 'app/controllers/import');
Util::addScript('passman', 'app/controllers/export');
Util::addScript('passman', 'app/controllers/generic-csv-importer');
Util::addScript('passman', 'app/controllers/vaultreqdeletion');
Util::addScript('passman', 'app/filters/range');
Util::addScript('passman', 'app/filters/propsfilter');
Util::addScript('passman', 'app/filters/byte');
Util::addScript('passman', 'app/filters/tagfilter');
Util::addScript('passman', 'app/filters/escapeHTML');
Util::addScript('passman', 'app/filters/as');
Util::addScript('passman', 'app/filters/credentialsearch');
Util::addScript('passman', 'app/filters/toHHMMSS');
Util::addScript('passman', 'app/services/cacheservice');
Util::addScript('passman', 'app/services/vaultservice');
Util::addScript('passman', 'app/services/credentialservice');
Util::addScript('passman', 'app/services/settingsservice');
Util::addScript('passman', 'app/services/fileservice');
Util::addScript('passman', 'app/services/encryptservice');
Util::addScript('passman', 'app/services/iconservice');
Util::addScript('passman', 'app/services/tagservice');
Util::addScript('passman', 'app/services/notificationservice');
Util::addScript('passman', 'app/services/shareservice');
Util::addScript('passman', 'app/services/searchboxexpanderservice');
Util::addScript('passman', 'app/factory/sharingacl');
Util::addScript('passman', 'app/directives/passwordgen');
Util::addScript('passman', 'app/directives/fileselect');
Util::addScript('passman', 'app/directives/progressbar');
Util::addScript('passman', 'app/directives/otp');
Util::addScript('passman', 'app/directives/qrreader');
Util::addScript('passman', 'app/directives/tooltip');
Util::addScript('passman', 'app/directives/use-theme');
Util::addScript('passman', 'app/directives/credentialfield');
Util::addScript('passman', 'app/directives/ngenter');
Util::addScript('passman', 'app/directives/autoscroll');
Util::addScript('passman', 'app/directives/clickselect');
Util::addScript('passman', 'app/directives/colorfromstring');
Util::addScript('passman', 'app/directives/credentialcounter');
Util::addScript('passman', 'app/directives/clearbutton2');
Util::addScript('passman', 'app/directives/credentialtemplate');
Util::addScript('passman', 'app/directives/clickdisable');
Util::addScript('passman', 'app/directives/icon');
Util::addScript('passman', 'app/directives/iconpicker');
Util::addScript('passman', 'importers/import-main');
Util::addScript('passman', 'importers/importer-keepasscsv');
Util::addScript('passman', 'importers/importer-lastpasscsv');
Util::addScript('passman', 'importers/importer-dashlanecsv');
Util::addScript('passman', 'importers/importer-zohocsv');
Util::addScript('passman', 'importers/importer-passmanjson');
Util::addScript('passman', 'importers/importer-ocpasswords');
Util::addScript('passman', 'importers/importer-clipperz');
Util::addScript('passman', 'importers/importer-teampass');
Util::addScript('passman', 'importers/importer-enpass');
Util::addScript('passman', 'importers/importer-passpackcsv');
Util::addScript('passman', 'importers/importer-randomdata');
Util::addScript('passman', 'importers/importer-padlock');
Util::addScript('passman', 'exporters/exporter-main');
Util::addScript('passman', 'exporters/exporter-csv');
Util::addScript('passman', 'exporters/exporter-json');
/*build-js-end*/

/*
 * Styles
 */
/*build-css-start*/
Util::addStyle('passman', 'vendor/ng-password-meter/ng-password-meter');
Util::addStyle('passman', 'vendor/bootstrap/bootstrap.min');

Util::addStyle('passman', 'vendor/font-awesome/font-awesome.min');
Util::addStyle('passman', 'vendor/angular-xeditable/xeditable.min');
Util::addStyle('passman', 'vendor/ng-tags-input/ng-tags-input.min');
Util::addStyle('passman', 'vendor/angularjs-datetime-picker/angularjs-datetime-picker');
Util::addStyle('passman', 'app');
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
