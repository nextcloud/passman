<?php
/** @var \OCP\IL10N $l */
/** @var array $_ */

script('passman', 'settings-admin');

style('passman', 'admin');
style('passman', 'vendor/font-awesome/font-awesome.min');
?>

<div id="passwordSharingSettings" class="followup section">
	<h2><?php p($l->t('Passman Settings')); ?></h2>
	<?php
	if ($_['checkVersion']) {
		p($l->t('GitHub version:') . ' ' . $_['githubVersion']);
		print '<br />';
	} ?>
	Local version: <?php p($_['localVersion']); ?><br/>
	<?php
	if ($_['checkVersion'] && version_compare($_['githubVersion'], $_['localVersion']) === 1) {
		p($l->t('A newer version of Passman is available'));
	}
	?>
	<div id="passman-tabs">
		<ul>
			<li>
				<a href="#general"><?php p($l->t('General settings')); ?></a>
			</li>
			<li>
				<a href="#sharing"><?php p($l->t('Password sharing')); ?></a>
			</li>
			<li>
				<a href="#mover"><?php p($l->t('Credential mover')); ?></a>
			</li>
			<li>
				<a href="#tabs-3"><?php p($l->t('Vault destruction requests')); ?></a>
			</li>
		</ul>
		<div id="general">
			<form name="passman_settings">
				<p>
					<input type="checkbox" name="check_version"
						   id="passman_check_version" class="checkbox"
						   value="0"/>
					<label for="passman_check_version">
						<?php p($l->t('Check for new versions')); ?>
					</label>
				</p>
				<p>
					<input type="checkbox" name="https_check"
						   id="passman_https_check" class="checkbox"
						   value="0"/>
					<label for="passman_https_check">
						<?php p($l->t('Enable HTTPS check')); ?>
					</label>
				</p>
				<p>
					<input type="checkbox" name="disable_contextmenu"
						   id="passman_disable_contextmenu" class="checkbox"
						   value="0"/>
					<label for="passman_disable_contextmenu">
						<?php p($l->t('Disable context menu')); ?>
					</label>
				</p>
				<p>
					<input type="checkbox" name="passman_disable_debugger"
						   id="passman_disable_debugger" class="checkbox"
						   value="0"/>
					<label for="passman_disable_debugger">
						<?php p($l->t('Disable JavaScript debugger')); ?>
					</label>
				</p>
				<p>
					<input type="checkbox" name="passman_enable_global_search"
						   id="passman_enable_global_search" class="checkbox"
						   value="0"/>
					<label for="passman_enable_global_search">
						<?php p($l->t('Enable global search')); ?>
					</label>
				</p>
				<p>
					<label for="vault_key_strength">Minimum vault key
						strength:</label>
					<select name="vault_key_strength" id="vault_key_strength">
						<option value="0">
							Poor
						</option>
						<option value="2">
							Weak
						</option>
						<option value="3">
							Good
						</option>
						<option value="4">
							Strong
						</option>
					</select>
				</p>
			</form>
		</div>
		<div id="sharing">
			<p>
				<input type="checkbox" name="passman_link_sharing_enabled"
					   id="passman_link_sharing_enabled" class="checkbox"
					   value="1"/>
				<label for="passman_link_sharing_enabled">
					<?php p($l->t('Allow users on this server to share passwords with a link')); ?>
				</label>
			</p>

			<p>
				<input type="checkbox" name="passman_sharing_enabled"
					   id="passman_sharing_enabled" class="checkbox"
					   value="1"/>
				<label for="passman_sharing_enabled">
					<?php p($l->t('Allow users on this server to share passwords with other users')); ?>
				</label>
			</p>
		</div>
		<div id="mover">
			<p><?php p($l->t('Move credentials from one account to another')); ?></p>
			<br/>
			<table class="table">
				<tr>
					<td><?php p($l->t('Source account')); ?> </td>
					<td><input type="hidden" class="form-control account_mover_selector" id="source_account"></td>
				</tr>
				<tr>
					<td><?php p($l->t('Destination account')); ?> </td>
					<td><input type="hidden" class="form-control account_mover_selector" id="destination_account"></td>
				</tr>
			</table>
			<button class="success" id="move_credentials">Move</button>
			<span id="moveStatusSucceeded" style="display: none;"><?php p($l->t('Credentials moved!')); ?></span>
            <span id="moveStatusFailed" style="display: none;"><?php p($l->t('An error occurred!')); ?></span>

		</div>
		<div id="tabs-3">
			<?php p($l->t('Requests to destroy vault')); ?>
			<table id="requests-table">
				<thead>
				<tr>
					<th><?php p($l->t('Request ID')); ?></th>
					<th><?php p($l->t('Requested by')); ?></th>
					<th><?php p($l->t('Reason')); ?></th>
					<th><?php p($l->t('Created')); ?></th>
					<th></th>
				</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>

	</div>
</div>
