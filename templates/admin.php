<?php
/** @var \OCP\IL10N $l */
/** @var array $_ */

use OCP\Util;

include_once '_config.php';

/*build-js-start*/
Util::addScript(MyAppTemplateConfig::APP_ID, 'vendor/jquery-3.7.1.min', 'core');
/*build-js-end*/

Util::addScript(MyAppTemplateConfig::APP_ID, 'settings-admin', 'core');

style(MyAppTemplateConfig::APP_ID, 'admin');
?>

<div id="passwordSharingSettings" class="passman-admin section">
	<h2><?php p($l->t('Passman Settings')); ?></h2>

	<div class="passman-admin-version settings-hint">
		<?php
		if ($_['checkVersion']) {
			if ($_['githubReleaseUrl']) {
				p($l->t('GitHub version:'));
				?>
				<a target="_blank" rel="noreferrer noopener" class="external" href="<?php p($_['githubReleaseUrl']); ?>"><?php p($_['githubVersion']); ?> ↗</a>
				<?php
			} else {
				p($l->t('GitHub version:') . ' ' . $_['githubVersion']);
			}
			print '<br />';
		} ?>
		<?php p($l->t('Local version:')); ?> <?php p($_['localVersion']); ?><br/>
		<?php
		if ($_['checkVersion'] && version_compare($_['githubVersion'], $_['localVersion']) === 1) {
			p($l->t('A newer version of Passman is available'));
		}
		?>
	</div>

	<div id="passman-tabs" class="passman-admin-tabs">
		<div class="tabHeaders" role="tablist">
			<a href="#general" class="tabHeader selected" role="tab" data-tab="general" aria-selected="true">
				<?php p($l->t('General settings')); ?>
			</a>
			<a href="#sharing" class="tabHeader" role="tab" data-tab="sharing" aria-selected="false">
				<?php p($l->t('Password sharing')); ?>
			</a>
			<a href="#mover" class="tabHeader" role="tab" data-tab="mover" aria-selected="false">
				<?php p($l->t('Credential mover')); ?>
			</a>
			<a href="#deletion-requests" class="tabHeader" role="tab" data-tab="deletion-requests" aria-selected="false">
				<?php p($l->t('Vault destruction requests')); ?>
			</a>
		</div>

		<div class="tabsContainer">
			<div id="general" class="tab" role="tabpanel" data-tab="general">
				<form name="passman_settings" class="passman-admin-form">
					<div class="passman-admin-setting">
						<input type="checkbox" name="check_version"
							   id="passman_check_version" class="checkbox"
							   value="0"/>
						<label for="passman_check_version">
							<?php p($l->t('Check for new versions')); ?>
						</label>
					</div>
					<div class="passman-admin-setting">
						<input type="checkbox" name="https_check"
							   id="passman_https_check" class="checkbox"
							   value="0"/>
						<label for="passman_https_check">
							<?php p($l->t('Enable HTTPS check')); ?>
						</label>
					</div>
					<div class="passman-admin-setting">
						<input type="checkbox" name="disable_contextmenu"
							   id="passman_disable_contextmenu" class="checkbox"
							   value="0"/>
						<label for="passman_disable_contextmenu">
							<?php p($l->t('Disable context menu')); ?>
						</label>
					</div>
					<div class="passman-admin-setting">
						<input type="checkbox" name="passman_disable_debugger"
							   id="passman_disable_debugger" class="checkbox"
							   value="0"/>
						<label for="passman_disable_debugger">
							<?php p($l->t('Disable JavaScript debugger')); ?>
						</label>
					</div>
					<div class="passman-admin-setting">
						<input type="checkbox" name="passman_enable_global_search"
							   id="passman_enable_global_search" class="checkbox"
							   value="0"/>
						<label for="passman_enable_global_search">
							<?php p($l->t('Enable global search')); ?>
						</label>
					</div>
					<div class="passman-admin-setting passman-admin-setting--select">
						<label for="vault_key_strength">
							<?php p($l->t('Minimum vault key strength')); ?>
						</label>
						<select name="vault_key_strength" id="vault_key_strength" class="passman-admin-select">
							<option value="0"><?php p($l->t('Poor')); ?></option>
							<option value="2"><?php p($l->t('Weak')); ?></option>
							<option value="3"><?php p($l->t('Good')); ?></option>
							<option value="4"><?php p($l->t('Strong')); ?></option>
						</select>
					</div>
				</form>
			</div>

			<div id="sharing" class="tab hidden" role="tabpanel" data-tab="sharing">
				<div class="passman-admin-form">
					<div class="passman-admin-setting">
						<input type="checkbox" name="passman_link_sharing_enabled"
							   id="passman_link_sharing_enabled" class="checkbox"
							   value="1"/>
						<label for="passman_link_sharing_enabled">
							<?php p($l->t('Allow users on this server to share passwords with a link')); ?>
						</label>
					</div>
					<div class="passman-admin-setting">
						<input type="checkbox" name="passman_sharing_enabled"
							   id="passman_sharing_enabled" class="checkbox"
							   value="1"/>
						<label for="passman_sharing_enabled">
							<?php p($l->t('Allow users on this server to share passwords with other users')); ?>
						</label>
					</div>
				</div>
			</div>

			<div id="mover" class="tab hidden" role="tabpanel" data-tab="mover">
				<p class="settings-hint"><?php p($l->t('Move credentials from one account to another')); ?></p>

				<div class="passman-admin-mover">
					<div class="passman-admin-form-row">
						<label for="source_account_input"><?php p($l->t('Source account')); ?></label>
						<div class="passman-admin-user-search-wrap">
							<input type="text"
								   id="source_account_input"
								   class="passman-admin-input account_mover_input"
								   autocomplete="off"
								   placeholder="<?php p($l->t('Search for a user')); ?>"/>
							<input type="hidden" id="source_account" class="account_mover_selector"/>
							<ul class="passman-admin-user-dropdown hidden" role="listbox" aria-label="<?php p($l->t('Source account')); ?>"></ul>
						</div>
					</div>
					<div class="passman-admin-form-row">
						<label for="destination_account_input"><?php p($l->t('Destination account')); ?></label>
						<div class="passman-admin-user-search-wrap">
							<input type="text"
								   id="destination_account_input"
								   class="passman-admin-input account_mover_input"
								   autocomplete="off"
								   placeholder="<?php p($l->t('Search for a user')); ?>"/>
							<input type="hidden" id="destination_account" class="account_mover_selector"/>
							<ul class="passman-admin-user-dropdown hidden" role="listbox" aria-label="<?php p($l->t('Destination account')); ?>"></ul>
						</div>
					</div>
				</div>

				<div class="passman-admin-actions">
					<button type="button" class="primary" id="move_credentials" data-label="<?php p($l->t('Move')); ?>">
						<?php p($l->t('Move')); ?>
					</button>
					<span id="moveStatusSucceeded" class="passman-admin-status passman-admin-status--success hidden">
						<?php p($l->t('Credentials moved!')); ?>
					</span>
					<span id="moveStatusFailed" class="passman-admin-status passman-admin-status--error hidden">
						<?php p($l->t('An error occurred!')); ?>
					</span>
				</div>
			</div>

			<div id="deletion-requests" class="tab hidden" role="tabpanel" data-tab="deletion-requests">
				<p class="settings-hint"><?php p($l->t('Requests to destroy vault')); ?></p>
				<div class="passman-admin-table-wrap">
					<table id="requests-table" class="passman-admin-table">
						<thead>
						<tr>
							<th><?php p($l->t('Request ID')); ?></th>
							<th><?php p($l->t('Requested by')); ?></th>
							<th><?php p($l->t('Reason')); ?></th>
							<th><?php p($l->t('Created')); ?></th>
							<th><?php p($l->t('Actions')); ?></th>
						</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
