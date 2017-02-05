<?php
/*
 * Javascripts
 */
script('passman', 'passman.min');

/*
 * Styles
 */
style('passman', 'passman.min');
style('passman', 'public-page');

?>
<div ng-app="passmanApp" ng-controller="PublicSharedCredential">
	<div class="row">
		<div class="col-xs-8 col-xs-push-2 col-xs-pull-2 credential_container">
			<h2>Passman</h2>
			<div ng-if="!shared_credential && !expired">
				<span class="text">Someone has shared a credential with you.</span>
				<button class="button-geen" ng-if="!loading"
						ng-click="loadSharedCredential()">Click here to request
					it
				</button>
				<button class="button-geen" ng-if="loading"><i
							class="fa fa-spinner fa-spin"></i>Loading...
				</button>
			</div>
			<div ng-if="expired">
				Awwhh.... credential not found. Maybe it expired
			</div>
			<div ng-if="shared_credential">
				<table class="table">
					<tr ng-show="shared_credential.label">
						<td>
							Label
						</td>
						<td>
							{{shared_credential.label}}
						</td>
					</tr>
					<tr ng-show="shared_credential.username">
						<td>
							Account
						</td>
						<td>
					<span credential-field
						  value="shared_credential.username"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.password">
						<td>
							Password
						</td>
						<td>
					<span credential-field value="shared_credential.password"
						  secret="'true'"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.otp.secret">
						<td>
							OTP
						</td>
						<td>
					<span otp-generator
						  secret="shared_credential.otp.secret"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.email">
						<td>
							E-mail
						</td>
						<td>
					<span credential-field
						  value="shared_credential.email"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.url">
						<td>
							URL
						</td>
						<td>
					<span credential-field
						  value="shared_credential.url"></span>
						</td>
					</tr>
					<tr ng-show="shared_credential.files.length > 0">
						<td>
							Files
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
							Expire time
						</td>
						<td>
							{{shared_credential.expire_time * 1000 |
							date:'dd-MM-yyyy @ HH:mm:ss'}}
						</td>
					</tr>
					<tr ng-show="shared_credential.changed">
						<td>
							Changed
						</td>
						<td>
							{{shared_credential.changed * 1000 |
							date:'dd-MM-yyyy @ HH:mm:ss'}}
						</td>
					</tr>
					<tr ng-show="shared_credential.created">
						<td>
							Created
						</td>
						<td>
							{{shared_credential.created * 1000 |
							date:'dd-MM-yyyy @ HH:mm:ss'}}
						</td>
					</tr>

				</table>

				<div class="tags">
					<span class="tag" ng-repeat="tag in shared_credential.tags">{{tag.text}}</span>

				</div>
			</div>
			<div class="footer">
				<a href="https://github.com/nextcloud/passman" target="_blank"
				   class="link">Github</a> | <a
						href="https://github.com/nextcloud/passman/wiki"
						target="_blank" class="link">Wiki</a> | <a
						href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6YS8F97PETVU2"
						target="_blank" class="link">Donate</a>
			</div>
		</div>
	</div>
</div>