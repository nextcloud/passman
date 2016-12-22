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

	<div class="warning_bar" ng-if="using_http && http_warning_hidden == false">
		{{ 'http.warning' | translate }}
		<i class="fa fa-times fa-2x" alt="Close" ng-click="setHttpWarning(true);"></i>
	</div>

	<div id="app-navigation" ng-if="selectedVault" ng-controller="MenuCtrl">
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
