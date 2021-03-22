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
use OCP\IL10N;

class TranslationController extends ApiController {
	private $trans;

	public function __construct($AppName,
								IRequest $request,
								IL10N $trans
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
		$this->trans = $trans;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function getLanguageStrings() {
		$translations = array(
			// js/app/controllers/bookmarklet.js
			'generating.sharing.keys' =>  $this->trans->t('Generating sharing keys ( %s / 2)','%step'),
			'invalid.vault.key' => $this->trans->t('Incorrect vault password!'),
			'password.do.not.match' => $this->trans->t('Passwords do not match'),
			'general' => $this->trans->t('General'),
			'custom.fields' => $this->trans->t('Custom Fields'),
			'error.no.label' => $this->trans->t('Please fill in a label.'),
			'error.no.value' => $this->trans->t('Please fill in a value.'),
			'error.loading.file' => $this->trans->t('Error loading file'),

			// js/app/controllers/credential.js
			'error.decrypt' => $this->trans->t('An error occurred during decryption'),
			'credential.created' => $this->trans->t('Credential created!'),
			'credential.deleted' => $this->trans->t('Credential deleted'),
			'credential.updated' => $this->trans->t('Credential updated'),
			'credential.recovered' => $this->trans->t('Credential recovered'),
			'credential.destroyed' => $this->trans->t('Credential destroyed'),
			'error.loading.file.perm' => $this->trans->t('Error downloading file, you probably have insufficient permissions'),

			// js/app/controllers/edit_credential.js
			'invalid.qr' => $this->trans->t('Invalid QR code'),

			// js/app/controllers/export.js
			'export.starting' => $this->trans->t('Starting export'),
			'export.decrypt' => $this->trans->t('Decrypting credentials'),
			'done' => $this->trans->t('Done'),

			// js/app/controllers/import.js
			'import.file.read' => $this->trans->t('File read.'),
			'import.steps' => $this->trans->t('Proceed with the following steps to import your file'),

			'import.no.label' => $this->trans->t('Skipping unlabeled credential'),
			'import.adding' => $this->trans->t('Adding {{credential}}'),
			'import.added' => $this->trans->t('Added {{credential}}'),
			'import.skipping' => $this->trans->t('Skipping credential, missing label on line {{line}}'),
			'import.loaded' => $this->trans->t('Parsed {{num}} credentials, starting to import'),
			'import.importing' => $this->trans->t('Importing'),
			'import.start' => $this->trans->t('Start import'),

			'select.csv' => $this->trans->t('Select CSV file'),
			'parsed.csv.rows' => $this->trans->t('Parsed {{rows}} lines from CSV file'),
			'skip.first.row' => $this->trans->t('Skip first row'),
			'import.csv.label.req' => $this->trans->t('You need to assign the label field before you can start the import.'),
			'first.five.lines' => $this->trans->t('The first 5 lines of the CSV are shown.'),
			'assign.column' => $this->trans->t('Assign the proper fields to each column.'),
			'example.credential' => $this->trans->t('Example of imported credential'),
			'missing.importer' => $this->trans->t('Missing an importer? Try it with the generic CSV importer.'),
			'missing.importer.back' => $this->trans->t('Go back to importers.'),


			// js/app/controllers/revision.js
			'revision.deleted' => $this->trans->t('Revision deleted'),
			'revision.restored' => $this->trans->t('Revision restored'),

			// js/app/controllers/settings.js
			'bookmarklet.text' => $this->trans->t('Save in Passman'),
			'settings.saved' => $this->trans->t('Settings saved'),
			'settings.general' => $this->trans->t('General settings'),
			'settings.audit' => $this->trans->t('Password audit'),
			'settings.password' => $this->trans->t('Password settings'),
			'settings.import' => $this->trans->t('Import credentials'),
			'settings.export' => $this->trans->t('Export credentials'),
			'settings.sharing' => $this->trans->t('Sharing'),
			'changepw.navigate.away.warning' => $this->trans->t('Are you sure you want to leave? This will destroy all your credentials'),
			'incorrect.password' => $this->trans->t('Old password field incorrect!'),
			'password.no.match' => $this->trans->t('New password does not match!'),
			'login.new.pass' => $this->trans->t('Please log in with your new vault password'),

			// js/app/controllers/share.js
			'share.u.g' => $this->trans->t('Share with users and groups'),
			'share.link' => $this->trans->t('Share link'),
			'share.navigate.away.warning' => $this->trans->t('Are you sure you want to leave? This will corrupt this credential'),
			'credential.unshared' => $this->trans->t('Credential unshared'),
			'credential.shared' => $this->trans->t('Credential shared'),
			'saved' => $this->trans->t('Saved!'),

			// js/app/controllers/vault.js
			'password.poor' => $this->trans->t('Poor'),
			'password.weak' => $this->trans->t('Weak'),
			'password.good' => $this->trans->t('Good'),
			'password.strong' => $this->trans->t('Strong'),
			// js/app/directives/credentialfield.js
			'toggle.visibility' => $this->trans->t('Toggle visibility'),
			'copy.field' => $this->trans->t('Copy to clipboard'),
			'copied' => $this->trans->t('Copied to clipboard!'),

			// js/app/directives/passwordgen.js
			'password.gen' => $this->trans->t('Generate password'),
			'password.copy' => $this->trans->t('Copy password to clipboard'),
			'password.copied' => $this->trans->t('Password copied to clipboard!'),

			// js/app/directives/progressbar.js
			'complete' => $this->trans->t('Complete'),


			// templates/views/partials/edit_credential/basics.html
			'username' => $this->trans->t('Username'),
			'password.r' => $this->trans->t('Repeat password'),
			'add.tag' => $this->trans->t('Add tag'),
			'pick.icon' => $this->trans->t('Pick an icon'),
            'pick.icon.search' => $this->trans->t('Search icons'),
            'pick.icon.custom.label' => $this->trans->t('Upload a custom icon:'),
			'use.icon' => $this->trans->t('Use this icon'),
			'use.icon.delete' => $this->trans->t('Delete current icon'),
			'use.icon.refresh' => $this->trans->t('Get icon from page'),
			'use.icon.refresh.trying' => $this->trans->t('This may take a few seconds…'),
			'use.icon.refresh.error' => $this->trans->t('There was an error fetching the icon!'),
			'selected.icon' => $this->trans->t('Selected icon'),

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
			'upload.qr' => $this->trans->t('Upload or enter your OTP secret'),
			'current.qr' => $this->trans->t('Current OTP settings'),
			'issuer' => $this->trans->t('Issuer'),
			'secret' => $this->trans->t('Secret'),


			// templates/views/partials/edit_credential/password.html
			'expire.date' => $this->trans->t('Expiration date'),
			'no.expire.date' => $this->trans->t('No expiration date set'),
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
			'export.confirm.text' => $this->trans->t('Enter vault password to confirm export.'),

			// templates/views/partials/forms/settings/general_settings.html
			'rename.vault' => $this->trans->t('Rename vault'),
			'rename.vault.name' => $this->trans->t('New vault name'),
			'change' => $this->trans->t('Change'),
			'change.vault.key' => $this->trans->t('Change vault key'),
			'old.vault.password' => $this->trans->t('Old vault password'),
			'new.vault.password' => $this->trans->t('New vault password'),
			'new.vault.pw.r' => $this->trans->t('Repeat new vault password'),
			'warning.leave' => $this->trans->t('Please wait your vault is being updated, do not leave this page.'),
			'processing' => $this->trans->t('Processing'),
			'total.progress' => $this->trans->t('Total progress'),
			'about.passman' => $this->trans->t('About Passman'),
			'version' => $this->trans->t('Version'),
			'donate.support' => $this->trans->t('Donate to support development'),
			'bookmarklet' => $this->trans->t('Bookmarklet'),
			'bookmarklet.info1' => $this->trans->t('Save your passwords with one click.'),
			'bookmarklet.info2' => $this->trans->t('Drag below button to your bookmark toolbar.'),
			'delete.vault' => $this->trans->t('Delete vault'),
			'vault.password' => $this->trans->t('Vault password'),
			'vault.remove.notice' => $this->trans->t('This process is irreversible'),
			'delete.vault.checkbox' => $this->trans->t('Delete my precious passwords'),
			'deleting.pw' => $this->trans->t('Deleting {{password}}…'),
			'delete.vault.confirm' => $this->trans->t('Yes, delete my precious passwords'),


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
			'tool.intro' => $this->trans->t('The password tool scans your password, calculates average cracking time, listing those below the threshold'),
			'min.strength' => $this->trans->t('Minimum password stength'),
			'scan.start' => $this->trans->t('Start scan'),
			'scan.result.msg' => $this->trans->t('Result'),
			'scan.result' => $this->trans->t('A total of {{scan_result}} weak credentials were found.'),
			'score' => $this->trans->t('Score'),
			'action' => $this->trans->t('Action'),

			// templates/vieuws/partials/forms/share_credential/basics.html
			'search.u.g' => $this->trans->t('Search users…'),
			'search.result.missing' => $this->trans->t('Missing users? Only users that have vaults are shown.'),
			'cyphering' => $this->trans->t('Cyphering'),
			'uploading' => $this->trans->t('Uploading'),
			'user' => $this->trans->t('User'),
			'crypto.time' => $this->trans->t('Crypto time'),
			'crypto.total.time' => $this->trans->t('Total time spent encrypting'),
			'perm.read' => $this->trans->t('Read'),
			'perm.write' => $this->trans->t('Write'),
			'perm.files' => $this->trans->t('Files'),
			'perm.revisions' => $this->trans->t('Revisions'),
			'pending' => $this->trans->t('Pending'),


			// templates/vieuws/partials/forms/share_credential/link_sharing.html
			'enable.link.sharing' => $this->trans->t('Enable link sharing'),
			'share.until.date' => $this->trans->t('Share until date'),
			'expire.views' => $this->trans->t('Expire after views'),
			'click.share' => $this->trans->t('Click "Share" first'),
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
			'alltime' => $this->trans->t('Beginning'),
			'number.filtered' => $this->trans->t('Showing {{number_filtered}} of {{credential_number}} credentials'),
			'search.credential' => $this->trans->t('Search for credential…'),
			'account' => $this->trans->t('Account'),
			'password' => $this->trans->t('Password'),
			'otp' => $this->trans->t('OTP'),
			'email' => $this->trans->t('E-mail'),
			'url' => $this->trans->t('URL'),
			'notes' => $this->trans->t('Notes'),
			'files' => $this->trans->t('Files'),
			'expire.time' => $this->trans->t('Expiry time'),
			'changed' => $this->trans->t('Changed'),
			'created' => $this->trans->t('Created'),
			'edit' => $this->trans->t('Edit'),
			'delete' => $this->trans->t('Delete'),
			'share' => $this->trans->t('Share'),
			'revisions' => $this->trans->t('Revisions'),
			'recover' => $this->trans->t('Recover'),
			'destroy' => $this->trans->t('Destroy'),
			'use.regex' => $this->trans->t('Use regex'),
			'sharereq.title' => $this->trans->t('You have incoming share requests.'),
			'sharereq.line1' => $this->trans->t('If you want to put the credential in another vault,'),
			'sharereq.line2' => $this->trans->t('log out of this vault and log into the vault you want the shared credential in.'),
			'permissions' => $this->trans->t('Permissions'),
			'received.from' => $this->trans->t('Received from'),
			'date' => $this->trans->t('Date'),
			'accept' => $this->trans->t('Accept'),
			'decline' => $this->trans->t('Decline'),
			'session.time.left' => $this->trans->t('You have {{session_time}} left before logout.'),
			'vault.locked' => $this->trans->t('Your vault has been locked for {{time}} because of {{tries}} failed attempts!'),
            'vault.hint.hello' => $this->trans->t('Hello there!'),
            'vault.hint.hello.add' => $this->trans->t('It does not seem that you have any passwords. Do you want to add one?'),
            'vault.hint.list.nogood' => $this->trans->t('You don\'t have good credentials'),
            'vault.hint.list.nomedium' => $this->trans->t('You don\'t have medium credentials'),
            'vault.hint.list.nobad' => $this->trans->t('You don\'t have bad credentials'),
            'vault.hint.list.noexpired' => $this->trans->t('You don\'t have expired credentials'),
            'vault.hint.list.nodeleted' => $this->trans->t('You don\'t have deleted credentials'),
            'vault.hint.list.notags' => $this->trans->t('There are no credentials with your selected tags'),
            'vault.hint.list.nosearch' => $this->trans->t('There are no credentials matching'),


			// templates/views/vaults.html
			'last.access' => $this->trans->t('Last accessed'),
			'never' => $this->trans->t('Never'),


			'no.vaults' => $this->trans->t('No vaults found, why not create one?'),
			'min.vault.key.strength' => $this->trans->t('Password strength must be at least: {{strength}}'),

			'new.vault.name' => $this->trans->t('Please give your new vault a name.'),
			'new.vault.pass' => $this->trans->t('Vault password'),
			'new.vault.passr' => $this->trans->t('Repeat vault password'),
			'new.vault.sharing_key_notice' => $this->trans->t('Your sharing keys will have a strength of 1024 bit, which you can change in "Settings" later.'),
			'new.vault.create' => $this->trans->t('Create vault'),
			'go.back.vaults' => $this->trans->t('Go back to vaults'),
			'input.vault.password' => $this->trans->t('Please input the password for'),
			'vault.default' => $this->trans->t('Set this vault as the default.'),
			'vault.auto.login' => $this->trans->t('Log into this vault automatically.'),
			'auto.logout' => $this->trans->t('Log out of this vault automatically after: '),
			'vault.decrypt' => $this->trans->t('Decrypt vault'),

			'req.intro1' => $this->trans->t('Seems you lost the vault password and you\'re unable to log in.'),
			'req.intro2' => $this->trans->t('If you want this vault to be removed you can request that here.'),
			'req.intro3' => $this->trans->t('An admin then accepts or declines the request'),

			'request.deletion.warning' => $this->trans->t('After an admin destroys this vault, all credentials in it will be lost'),
			'request.deletion.reason' => $this->trans->t('Reason for requesting deletion (optional):'),
			'request.deletion' => $this->trans->t('Request vault destruction'),
			'request.deletion.accept' => $this->trans->t('Yes, request an admin to destroy this vault'),
			'cancel.request.deletion' => $this->trans->t('Cancel destruction request'),
			'deletion.requested' => $this->trans->t('Vault destruction requested'),
			'deletion.removed' => $this->trans->t('Request removed'),
			'delete.request.pending' => $this->trans->t('Destruction request pending'),


			// templates/bookmarklet.php
			'http.warning' => $this->trans->t('Warning! Adding credentials over HTTP is insecure!'),
			'bm.active.vault' => $this->trans->t('Logged into {{vault_name}}'),
			'change.vault' => $this->trans->t('Change vault'),

			// templates/main.php
			'deleted.credentials' => $this->trans->t('Deleted credentials'),
			'logout' => $this->trans->t('Logout'),
			'donate' => $this->trans->t('Donate'),
            'navigation.show.all' => $this->trans->t('Show All'),
            'navigation.tags' => $this->trans->t('Tags'),
            'navigation.tags.search' => $this->trans->t('Search Tags'),
            'navigation.strength.good' => $this->trans->t('Good Strength'),
            'navigation.strength.medium' => $this->trans->t('Medium Strength'),
            'navigation.strength.bad' => $this->trans->t('Bad Strength'),
            'navigation.expired' => $this->trans->t('Expired'),
            'navigation.advanced.filter' => $this->trans->t('Filter Tags'),
            'navigation.advanced.checkbox' => $this->trans->t('Simple Navigation'),


			// templates/public_share.php
			'share.page.text' => $this->trans->t('Someone has shared a credential with you.'),
			'share.page.link' => $this->trans->t('Click here to request it'),
			'share.page.link_loading' => $this->trans->t('Loading…'),
			'expired.share' => $this->trans->t('Awwhh… credential not found. Maybe it expired'),

			//compromised credentials
			'compromised.label' => $this->trans->t('Mark as Compromised'),
			'compromised.warning.list' => $this->trans->t('Compromised!'),
			'compromised.warning' => $this->trans->t('This password is compromised. You can only remove this warning by changing the password.'),

			//searchboxexpanderservice
			'search.settings.input.label' => $this->trans->t('Label'),
			'search.settings.input.username' => $this->trans->t('Username'),
			'search.settings.input.email' => $this->trans->t('email'),
			'search.settings.input.custom_fields' => $this->trans->t('Custom Fields'),
			'search.settings.input.password' => $this->trans->t('Password'),
			'search.settings.input.description' => $this->trans->t('Description'),
			'search.settings.input.url' => $this->trans->t('URL'),

			'search.settings.title' => $this->trans->t('Custom Search:'),
			'search.settings.defaults_button' => $this->trans->t('Revert to defaults'),


		);
		return new JSONResponse($translations);
	}
}
