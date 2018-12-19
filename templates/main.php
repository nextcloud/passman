<?php
/*
 * Javascripts
 */
script('passman', 'passman.min');


/*
 * Styles
 */
style('passman', 'passman.min');
?>

<div id="app" ng-app="passmanApp" ng-controller="MainCtrl">
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
                <div class="app-navigation-entry-bullet bullet-color-red"></div>
                <a ng-class="{selected: clickedNavigationItem=='strength_low'}" ng-click="filterCredentialBySpecial('strength_low')">{{ 'navigation.strength.bad' | translate }}</a>
            </li>
            <li>
                <div class="app-navigation-entry-bullet bullet-color-yellow"></div>
                <a ng-class="{selected: clickedNavigationItem=='strength_medium'}" ng-click="filterCredentialBySpecial('strength_medium')">{{ 'navigation.strength.medium' | translate }}</a>
            </li>
            <li>
                <div class="app-navigation-entry-bullet bullet-color-green"></div>
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
            <div id="content" ng-view="">

            </div>
        </div>
    </div>
</div>
