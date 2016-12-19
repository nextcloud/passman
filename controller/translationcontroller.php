<?php
/**
 * Nextcloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Sander Brand <brantje@gmail.com>
 * @copyright Sander Brand 2016
 */

namespace OCA\Passman\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use \OCP\IL10N;

class TranslationController extends ApiController {
	private $trans;
	public function __construct($AppName,
								IRequest $request,
								IL10N $trans
								){
		parent::__construct($AppName, $request);
		$this->trans = $trans;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getLanguageStrings($lang){
		$translations = array(
			// templates/views/partials/edit_credential/basics.html
			'username' => $this->trans->t('Username'),
			'password.r' => $this->trans->t('Repeat password'),
			'add.tag' => $this->trans->t('Add Tag'),

			// templates/views/partials/edit_credential/custom_fields.html
			'field.label' => $this->trans->t('Field label'),
			'field.value' => $this->trans->t('Field value'),
			'select.file' => $this->trans->t('Choose a file'),
			'text' => $this->trans->t('Text'),
			'file' => $this->trans->t('File'),
			'add' => $this->trans->t('Add'),
			'value' => $this->trans->t('Value'),
			'type' => $this->trans->t('Type'),
			'actions' => $this->trans->t('Actions'),
			'empty' => $this->trans->t('Empty'),

			// templates/views/partials/edit_credential/files.html
			'file.name' => $this->trans->t('Filename'),
			'upload.date' => $this->trans->t('Upload date'),
			'size' => $this->trans->t('Size'),


			// templates/views/partials/edit_credential/otp.html
			'upload.qr' => $this->trans->t('Upload your OTP qr code'),
			'current.qr' => $this->trans->t('Current OTP settings'),
			'issuer' => $this->trans->t('Issuer'),
			'secret' => $this->trans->t('Secret'),


			// templates/views/partials/edit_credential/password.html
			'expire.date' => $this->trans->t('Expire date'),
			'no.expire.date' => $this->trans->t('No expire date set'),
			'renew.interval' => $this->trans->t('Renew interval'),
			'disabled' => $this->trans->t('Disabled'),
			'days' => $this->trans->t('Day(s)'),
			'weeks' => $this->trans->t('Week(s)'),
			'months' => $this->trans->t('Month(s)'),
			'years' => $this->trans->t('Year(s)'),
			'generation.settings' => $this->trans->t('Password generation settings'),
			'password.generation.length' => $this->trans->t('Password length'),
			'password.generation.min_digits' => $this->trans->t('Minimum amount of digits'),
			'password.generation.uppercase' => $this->trans->t('Use uppercase letters'),
			'password.generation.lowercase' => $this->trans->t('Use lowercase letters'),
			'password.generation.digits' => $this->trans->t('Use numbers'),
			'password.generation.special' => $this->trans->t('Use special characters'),
			'password.generation.ambiguous' => $this->trans->t('Avoid ambiguous characters'),
			'password.generation.require_same' => $this->trans->t('Require every character type'),


			// templates/views/partials/forms/settings/export.html
			'export.type' => $this->trans->t('Export type'),
			'export' => $this->trans->t('Export'),

			// templates/views/partials/forms/settings/general_settings.html
			'rename.vault' => $this->trans->t('Rename vault'),
			'rename.vault.name' => $this->trans->t('New vault name'),
			'change' => $this->trans->t('Change'),
			'change.vault.key' => $this->trans->t('Change vault key'),
			'old.vault.password' => $this->trans->t('Old vault password'),
			'new.vault.password' => $this->trans->t('New vault password'),
			'new.vault.pw.r' => $this->trans->t('New vault password repeat'),
			'warning.leave' => $this->trans->t('Please wait your vault is being updated, do not leave this page.'),
			'processing' => $this->trans->t('Processing'),
			'total.progress' => $this->trans->t('Total progress'),
			'about.passman' => $this->trans->t('About Passman'),
			'version' => $this->trans->t('Version'),
			'donate.support' => $this->trans->t('Donate to support development'),
			'bookmarklet' => $this->trans->t('Bookmarklet'),
			'bookmarklet.info1' => $this->trans->t('Save your passwords with 1 click!'),
			'bookmarklet.info2' => $this->trans->t('Drag below button to your bookmark toolbar.'),


			// templates/views/partials/forms/settings/import.html
			'import.type' => $this->trans->t('Import type'),
			'import' => $this->trans->t('Import'),
			'read.progress' => $this->trans->t('Read progress'),
			'upload.progress' => $this->trans->t('Upload progress'),

			// templates/views/partials/forms/settings/password_settings.html
			// inherent from other pages

			// templates/views/partials/forms/settings/sharing.html
			'priv.key' => $this->trans->t('Private Key'),
			'pub.key' => $this->trans->t('Public key'),
			'key.size' => $this->trans->t('Key size'),
			'save.keys' => $this->trans->t('Save keys'),
			'gen.keys' => $this->trans->t('Generate sharing keys'),
			'generating.keys' => $this->trans->t('Generating sharing keys'),

			// templates/views/partials/forms/settings/tool.html
			'tool.intro' => $this->trans->t('The password tool will scan your password, calculate the avarage crack time and, if below the threshold, show them'),
			'min.strength' => $this->trans->t('Minimum password stength'),
			'scan.result.msg' => $this->trans->t('Passman scanned your passwords, and here is the result.'),
			'scan.result' => $this->trans->t('A total of {{scan_result}} weak credentials.'),
			'score' => $this->trans->t('Score'),
			'action' => $this->trans->t('Action'),

			// templates/vieuws/partials/forms/share_credential/basics.html
			'search.u.g' => $this->trans->t('Search users or groups...'),
			'cyphering' => $this->trans->t('Cyphering'),
			'uploading' => $this->trans->t('Uploading'),
			'user' => $this->trans->t('User'),
			'crypto.time' => $this->trans->t('Crypto time'),
			'crypto.total.time' => $this->trans->t('Total time spent cyphering'),
			'perm.read' => $this->trans->t('Read'),
			'perm.write' => $this->trans->t('Write'),
			'perm.files' => $this->trans->t('Files'),
			'perm.revisions' => $this->trans->t('Revisions'),
			'pending' => $this->trans->t('Pending'),


			// templates/vieuws/partials/forms/share_credential/link_sharing.html
			'enable.link.sharing' => $this->trans->t('Enable link sharing'),
			'share.until.date' => $this->trans->t('Share until date'),
			'expire.views' => $this->trans->t('Expire after views'),
			'click.share' => $this->trans->t('Click share first'),
			'show.files' => $this->trans->t('Show files'),


			// templates/views/partials/password-meter.html
			'details' => $this->trans->t('Details'),
			'hide.details' => $this->trans->t('Hide details'),
			'password.score' => $this->trans->t('Password score'),
			'cracking.times' => $this->trans->t('Cracking times'),
			'cracking.time.100h' => $this->trans->t('100 / hour'),
			'cracking.time.100h.desc' => $this->trans->t('Throttled online attack'),
			'cracking.time.10s' => $this->trans->t('10 / second'),
			'cracking.time.10s.desc' => $this->trans->t('Unthrottled online attack'),
			'cracking.time.10ks' => $this->trans->t('10k / second'),
			'cracking.time.10ks.desc' => $this->trans->t('Offline attack, slow hash, many cores'),
			'cracking.time.10Bs' => $this->trans->t('10B / second'),
			'cracking.time.10Bs.desc' => $this->trans->t('Offline attack, fast hash, many cores'),
			'match.sequence' => $this->trans->t('Match sequence'),
			'match.sequence.link' => $this->trans->t('See match sequence'),
			'pattern' => $this->trans->t('Pattern'),
			'matched.word' => $this->trans->t('Matched word'),
			'dictionary.name' => $this->trans->t('Dictionary name'),
			'rank' => $this->trans->t('Rank'),
			'reversed' => $this->trans->t('Reversed'),
			'guesses' => $this->trans->t('Guesses'),
			'base.guesses' => $this->trans->t('Base guesses'),
			'uppercase.variations' => $this->trans->t('Uppercase variations'),
			'leet.variations' => $this->trans->t('l33t-variations'),

			// templates/views/credential_revisions.html
			'showing.revisions' => $this->trans->t('Showing revisions of'),
			'revision.of' => $this->trans->t('Revision of'),
			'revision.edited.by' => $this->trans->t('by'),
			'no.revisions' => $this->trans->t('No revisions found.'),
			'label' => $this->trans->t('Label'),
			'restore.revision' => $this->trans->t('Restore revision'),
			'delete.revision' => $this->trans->t('Delete revision'),

			// templates/views/edit_credential.html
			'edit.credential' => $this->trans->t('Edit credential'),
			'create.credential' => $this->trans->t('Create new credential'),
			'save' => $this->trans->t('Save'),
			'cancel' => $this->trans->t('Cancel'),

			// templates/views/settings.html
			'settings' => $this->trans->t('Settings'),

			// templates/views/share_credential.html
			'share.credential' => $this->trans->t('Share credential {{credential}}'),
			'unshare' => $this->trans->t('Unshare'),



			// templates/views/show_vault.html
			'deleted.since' => $this->trans->t('Showing deleted since'),
			'alltime' => $this->trans->t('All time'),
			'number.filtered' => $this->trans->t('Showing {{number_filtered}} of {{credential_number}} credentials'),
			'search.credential' => $this->trans->t('Search credential...'),
			'account' => $this->trans->t('Account'),
			'password' => $this->trans->t('Password'),
			'otp' => $this->trans->t('OTP'),
			'email' => $this->trans->t('E-mail'),
			'url' => $this->trans->t('URL'),
			'notes' => $this->trans->t('Notes'),
			'files' => $this->trans->t('Files'),
			'expire.time' => $this->trans->t('Expire time'),
			'changed' => $this->trans->t('Changed'),
			'created' => $this->trans->t('Created'),
			'edit' => $this->trans->t('Edit'),
			'delete' => $this->trans->t('Delete'),
			'share' => $this->trans->t('Share'),
			'revisions' => $this->trans->t('Revisions'),
			'recover' => $this->trans->t('Recover'),
			'destroy' => $this->trans->t('Destroy'),

			'sharereq.title' =>  $this->trans->t('You have incoming share requests.'),
			'sharereq.line1' =>  $this->trans->t('If you want to the credential in a other vault,'),
			'sharereq.line2' =>  $this->trans->t('logout of this vault and login to the vault you want the shared credential in.'),
			'permissions' =>  $this->trans->t('Permissions'),
			'received.from' =>  $this->trans->t('Received from'),
			'date' =>  $this->trans->t('Date'),
			'accept' =>  $this->trans->t('Accept'),
			'decline' =>  $this->trans->t('Decline'),

			// templates/views/vaults.html
			'last.access' => $this->trans->t('Last accessed'),
			'never' => $this->trans->t('Never'),
			'no.vaults' => $this->trans->t('No vaults found, why not create one?'),

			'new.vault.name' => $this->trans->t('Please give your new vault a name.'),
			'new.vault.pass' => $this->trans->t('Vault password'),
			'new.vault.passr' => $this->trans->t('Repeat vault password'),
			'new.vault.sharing_key_notice' => $this->trans->t('Your sharing key\'s will have a strength of 1024 bit, which you can change later in settings.'),
			'new.vault.create' =>  $this->trans->t('Create vault'),
			'go.back.vaults' =>  $this->trans->t('Go back to vaults'),
			'input.vault.password' =>  $this->trans->t('Please input the password for'),
			'vault.default' =>  $this->trans->t('Set this vault as default.'),
			'vault.auto.login' =>  $this->trans->t('Login automatically to this vault.'),
			'vault.decrypt' =>  $this->trans->t('Decrypt vault'),

			// templates/bookmarklet.php
			'http.warning' =>  $this->trans->t('Warning! Adding credentials over http can be insecure!'),
			'bm.active.vault' =>  $this->trans->t('Logged in to {{vault_name}}'),
			'change.vault' => $this->trans->t('Change vault'),

			// templates/main.php
			'deleted.credentials' => $this->trans->t('Deleted credentials'),
			'logout' => $this->trans->t('Logout'),
			'donate' => $this->trans->t('Donate'),

			// templates/public_share.php
			'share.page.text' => $this->trans->t('Someone has shared a credential with you.'),
			'share.page.link' => $this->trans->t('Click here to request it'),
			'share.page.link_loading' => $this->trans->t('Loading...'),
			'expired.share' => $this->trans->t('Awwhh.... credential not found. Maybe it expired'),

		);
		return new JSONResponse($translations);
	}
}