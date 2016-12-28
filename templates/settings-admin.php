<?php
/** @var \OCP\IL10N $l */
/** @var array $_ */
use \OCP\App;

script('passman', 'settings-admin');

$checkVersion = OC::$server->getConfig()->getAppValue('passman', 'check_version', '1') === '1';
$AppInstance = new App();
$localVersion = $AppInstance->getAppInfo("passman")["version"];
if ($checkVersion) {
// get latest master version
	$doc = new DOMDocument();
	$doc->load('https://raw.githubusercontent.com/nextcloud/passman/master/appinfo/info.xml');
	$root = $doc->getElementsByTagName("info");
	$version = false;
	$githubVersion = $l->t('Unable to get version info');
	foreach ($root as $element) {
		$versions = $element->getElementsByTagName("version");
		$version = $versions->item(0)->nodeValue;
	}
	if ($version) {
		$githubVersion = $version;
	}
}
?>

<div id="passwordSharingSettings" class="followupsection">
	<form name="passman_settings">
		<h2><?php p($l->t('Passman Settings')); ?></h2>
		<?php
		if ($checkVersion) {
			p($l->t('Github version:'). ' '. $githubVersion);
			print '<br />';
		} ?>
		Local version: <?php p($localVersion); ?><br/>
		<?php
		if (version_compare($githubVersion, $localVersion) === 1) {
			p($l->t('A newer version of passman is available'));
		}
		?>
		<h3><?php p($l->t('Sharing')); ?></h3>
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
				   value="1" <?php if ($_['user_sharing_enabled']) print_unescaped('checked="checked"'); ?> />
			<label for="passman_sharing_enabled">
				<?php p($l->t('Allow users on this server to share passwords with other users')); ?>
			</label>
		</p>
		<h3><?php p($l->t('General')); ?></h3>
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
			<label for="vault_key_strength">Minimum vault key strength:</label>
			<select name="vault_key_strength" id="vault_key_strength">
				<option value="1" <?php if ($_['vault_key_strength'] === 1) print_unescaped('selected="selected"'); ?>>
					Poor
				</option>
				<option value="2" <?php if ($_['vault_key_strength'] === 2) print_unescaped('selected="selected"'); ?>>
					Weak
				</option>
				<option value="3" <?php if ($_['vault_key_strength'] === 3) print_unescaped('selected="selected"'); ?>>
					Good
				</option>
				<option value="4" <?php if ($_['vault_key_strength'] === 4) print_unescaped('selected="selected"'); ?>>
					Strong
				</option>
			</select>
		</p>
	</form>
</div>
