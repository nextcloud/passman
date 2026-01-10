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
script(MyAppTemplateConfig::APP_ID, 'vendor/ng-password-meter/ng-password-meter');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-translate/angular-translate.min');
script(MyAppTemplateConfig::APP_ID, 'vendor/angular-translate/angular-translate-loader-url.min');
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
script(MyAppTemplateConfig::APP_ID, 'lib/promise');
script(MyAppTemplateConfig::APP_ID, 'lib/crypto_wrap');


script(MyAppTemplateConfig::APP_ID, 'app/app_public');
script(MyAppTemplateConfig::APP_ID, 'app/controllers/public_shared_credential');
script(MyAppTemplateConfig::APP_ID, 'app/filters/range');
script(MyAppTemplateConfig::APP_ID, 'app/filters/propsfilter');
script(MyAppTemplateConfig::APP_ID, 'app/filters/byte');
script(MyAppTemplateConfig::APP_ID, 'app/filters/escapeHTML');
script(MyAppTemplateConfig::APP_ID, 'app/services/vaultservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/credentialservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/settingsservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/fileservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/encryptservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/tagservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/notificationservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/shareservice');
script(MyAppTemplateConfig::APP_ID, 'app/services/urlservice');
script(MyAppTemplateConfig::APP_ID, 'app/directives/otp');
script(MyAppTemplateConfig::APP_ID, 'app/directives/tooltip');
script(MyAppTemplateConfig::APP_ID, 'app/directives/use-theme');
script(MyAppTemplateConfig::APP_ID, 'app/directives/credentialfield');
script(MyAppTemplateConfig::APP_ID, 'app/directives/ngenter');
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
style(MyAppTemplateConfig::APP_ID, 'public-page');

?>
<div class="share-controller" ng-app="passmanApp" ng-controller="PublicSharedCredential">
	<div class="share-container">
        <div class="row">
		<div class="credential_container">
			<h2>Passman</h2>
			<div ng-if="!shared_credential && !expired">
				<span class="text"><?php p($l->t("Someone has shared a credential with you.")); ?></span>
				<button class="button-geen" ng-if="!loading"
						ng-click="loadSharedCredential()"><?php p($l->t("Click here to request
					it")); ?>
				</button>
				<button class="button-geen" ng-if="loading"><i
							class="fa fa-spinner fa-spin"></i><?php p($l->t("Loading&hellip;")); ?>
				</button>
			</div>
			<div ng-if="expired">
				<?php p($l->t("Oops! Credential not found. Maybe it expired.")); ?>
			</div>
			<div ng-if="shared_credential">
				<table class="table">
					<tr ng-show="shared_credential.label">
						<td>
							<?php p($l->t("Label")); ?>
						</td>
						<td>
							{{shared_credential.label}}
						</td>
					</tr>
					<tr ng-show="shared_credential.username">
						<td>
							<?php p($l->t("Account")); ?>
						</td>
						<td>
					<span credential-field
						  value="shared_credential.username"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.password">
						<td>
							<?php p($l->t("Password")); ?>
						</td>
						<td>
					<span credential-field value="shared_credential.password"
						  secret="'true'"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.otp.secret">
						<td>
							<?php p($l->t("OTP")); ?>
						</td>
						<td>
					<span otp-generator
						  otp="shared_credential.otp"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.email">
						<td>
							<?php p($l->t("E-mail")); ?>
						</td>
						<td>
					<span credential-field
						  value="shared_credential.email"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.url">
						<td>
							<?php p($l->t("URL")); ?>
						</td>
						<td class="whitespace_normal_field">
					<span credential-field url="true"
						  value="shared_credential.url"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.files.length > 0">
						<td>
							<?php p($l->t("Files")); ?>
						</td>
						<td>
							<div ng-repeat="file in shared_credential.files"
								 class="link"
								 ng-click="downloadFile(shared_credential, file)">
								{{file.filename}} ({{file.size | bytes}})
							</div>
						</td>
					</tr>
					<tr ng-repeat="field in shared_credential.custom_fields">
						<td>
							{{field.label}}
						</td>
						<td>
							<span credential-field value="field.value" secret="field.secret" ng-if="field.field_type !== 'file' || !field.field_type"></span>
							<span ng-if="field.field_type === 'file'" class="link" ng-click="downloadFile(shared_credential, field.value)">{{field.value.filename}} ({{field.value.size | bytes}})</span>
						</td>
					</tr>
					<tr ng-show="shared_credential.expire_time > 0">
						<td>
							<?php p($l->t("Expires:")); ?>
						</td>
						<td>
							{{shared_credential.expire_time * 1000 |
							date:'dd-MM-yyyy @ HH:mm:ss'}}
						</td>
					</tr>
					<tr ng-show="shared_credential.changed">
						<td>
							<?php p($l->t("Changed")); ?>
						</td>
						<td>
							{{shared_credential.changed * 1000 |
							date:'dd-MM-yyyy @ HH:mm:ss'}}
						</td>
					</tr>
					<tr ng-show="shared_credential.created">
						<td>
							<?php p($l->t("Created")); ?>
						</td>
						<td>
							{{shared_credential.created * 1000 |
							date:'dd-MM-yyyy @ HH:mm:ss'}}
						</td>
					</tr>
					<tr ng-show="shared_credential.tags && shared_credential.tags.length > 0">
						<td>
							<?php p($l->t("Tags")); ?>
						</td>
						<td class="tags">
							<span class="tag" ng-repeat="tag in shared_credential.tags">{{tag.text}}</span>
						</td>
					</tr>
				</table>
			</div>
			<div class="footer">
				<a href="https://github.com/nextcloud/passman" target="_blank"
				   class="link">GitHub</a> | <a
						href="https://github.com/nextcloud/passman/wiki"
						target="_blank" class="link">Wiki</a> | <a
						href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6YS8F97PETVU2"
						target="_blank" class="link">Donate</a>
			</div>
		</div>
	</div>
    </div>
</div>
