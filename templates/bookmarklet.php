<?php
/*
 * Javascripts
 */
script('passman', 'passman.min');

/*
 * Styles
 */
style('passman', 'passman.min');

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
