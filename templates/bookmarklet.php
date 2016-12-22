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
			<div class="active_vault">
				{{ 'bm.active.vault' | translate}} {{active_vault.name}}<br />
				<span class="link" ng-click="logout()">{{ 'change.vault' | translate }}</span>
			</div>
			<ul class="tab_header">
				<li ng-repeat="tab in tabs track by $index" class="tab"
					ng-class="{active:isActiveTab(tab)}"
					ng-click="onClickTab(tab)" use-theme
				>{{tab.title}}
				</li>
			</ul>

			<div class="tab_container edit_credential">
				<div ng-include="currentTab.url"></div>
				<button ng-click="saveCredential()">{{ 'save' | translate }}</button>
				<button ng-click="cancel()">{{ 'cancel' | translate }}</button>
			</div>
		</div>
	</div>
</div>