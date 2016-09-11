<?php
/*
 * Javascripts
 */
script('passman', 'vendor/angular/angular.min');
script('passman', 'vendor/angular-animate/angular-animate.min');
script('passman', 'vendor/angular-cookies/angular-cookies.min');
script('passman', 'vendor/angular-resource/angular-resource.min');
script('passman', 'vendor/angular-route/angular-route.min');
script('passman', 'vendor/angular-sanitize/angular-sanitize.min');
script('passman', 'vendor/angular-touch/angular-touch.min');
script('passman', 'vendor/angular-local-storage/angular-local-storage.min');
script('passman', 'vendor/sjcl/sjcl');


script('passman', 'app/app');
script('passman', 'templates');
script('passman', 'app/controllers/main');
script('passman', 'app/controllers/vault');
script('passman', 'app/filters/propsfilter');
script('passman', 'app/services/cacheservice');
script('passman', 'app/services/vaultservice');
script('passman', 'app/services/credentialservice');
script('passman', 'app/services/settingsservice');
script('passman', 'app/services/encryptservice');


/*
 * Styles
 */
style('passman', 'app');
?>

<div id="app" ng-app="passmanApp" ng-controller="MainCtrl">
	<div id="app-navigation" ng-if="selectedVault">
		<ul>
			<li><a href="#">First level entry</a></li>
			<li>
				<a href="#">First level container</a>
				<ul>
					<li><a href="#">Second level entry</a></li>
					<li><a href="#">Second level entry</a></li>
				</ul>
			</li>
		</ul>
		<div id="app-settings">
			<div id="app-settings-header">
				<button class="settings-button"
						data-apps-slide-toggle="#app-settings-content"
				></button>
			</div>
			<div id="app-settings-content">
				<!-- Your settings in here -->
			</div>
		</div>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper" ng-view="">

		</div>
	</div>
</div>
