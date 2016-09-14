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
script('passman', 'vendor/angular-off-click/angular-off-click.min');
script('passman', 'vendor/ng-password-meter/ng-password-meter');
script('passman', 'vendor/sjcl/sjcl');
script('passman', 'vendor/zxcvbn/zxcvbn');
script('passman', 'vendor/ng-clipboard/clipboard.min');
script('passman', 'vendor/ng-clipboard/ngclipboard');
script('passman', 'vendor/angular-xeditable/xeditable.min');
script('passman', 'vendor/sha/sha');
script('passman', 'vendor/llqrcode/llqrcode');


script('passman', 'app/app');
script('passman', 'templates');
script('passman', 'app/controllers/main');
script('passman', 'app/controllers/menu');
script('passman', 'app/controllers/vault');
script('passman', 'app/controllers/credential');
script('passman', 'app/controllers/edit_credential');
script('passman', 'app/filters/range');
script('passman', 'app/filters/propsfilter');
script('passman', 'app/filters/byte');
script('passman', 'app/services/cacheservice');
script('passman', 'app/services/vaultservice');
script('passman', 'app/services/credentialservice');
script('passman', 'app/services/settingsservice');
script('passman', 'app/services/fileservice');
script('passman', 'app/services/encryptservice');
script('passman', 'app/directives/passwordgen');
script('passman', 'app/directives/fileselect');
script('passman', 'app/directives/progressbar');
script('passman', 'app/directives/otp');
script('passman', 'app/directives/qrreader');
script('passman', 'app/directives/tooltip');
script('passman', 'app/directives/use-theme');
script('passman', 'app/directives/credentialfield');


/*
 * Styles
 */
style('passman', 'app');
style('passman', 'vendor/ng-password-meter/ng-password-meter');
style('passman', 'vendor/bootstrap/bootstrap.min');
style('passman', 'vendor/bootstrap/bootstrap-theme.min');
style('passman', 'vendor/font-awesome/font-awesome.min');
style('passman', 'vendor/angular-xeditable/xeditable.min');
?>

<div id="app" ng-app="passmanApp" ng-controller="MainCtrl">
	<div id="app-navigation" ng-if="selectedVault" ng-controller="MenuCtrl">
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
		<div id="app-settings" ng-init="settingsShown = false;">
			<div id="app-settings-header">
				<button class="settings-button"
						ng-click="settingsShown = !settingsShown"
				>Settings</button>
			</div>
			<div id="app-settings-content" ng-show="settingsShown">
				<!-- Your settings in here -->
				<div class="settings-container">
					<div><span class="link">Settings</span></div>
					<div><span class="link" ng-click="logout()">Logout</span></div>
				</div>
			</div>
		</div>
	</div>

	<div id="app-content" ng-class="{'with-app-sidebar': app_sidebar}">
		<div id="app-content-wrapper" ng-view="">

		</div>
	</div>
</div>
