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

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Passman\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#bookmarklet', 'url' => '/bookmarklet', 'verb' => 'GET'],
		['name' => 'page#publicSharePage', 'url' => '/share/public', 'verb' => 'GET'],

		//Vault
		['name' => 'vault#listVaults', 'url' => '/api/v2/vaults', 'verb' => 'GET'],
		['name' => 'vault#create', 'url' => '/api/v2/vaults', 'verb' => 'POST'],
		['name' => 'vault#get', 'url' => '/api/v2/vaults/{vault_id}', 'verb' => 'GET'],
		['name' => 'vault#update', 'url' => '/api/v2/vaults/{vault_id}', 'verb' => 'PATCH'],
		['name' => 'vault#delete', 'url' => '/api/v2/vaults/{vault_id}', 'verb' => 'DELETE'],
		//@TODO make frontend use PATCH
		['name' => 'vault#updateSharingKeys', 'url' => '/api/v2/vaults/{vault_id}/sharing-keys', 'verb' => 'POST'],

		//Credential
		['name' => 'credential#createCredential', 'url' => '/api/v2/credentials', 'verb' => 'POST'],
		['name' => 'credential#getCredential', 'url' => '/api/v2/credentials/{credential_id}', 'verb' => 'GET'],
		['name' => 'credential#updateCredential', 'url' => '/api/v2/credentials/{credential_id}', 'verb' => 'PATCH'],
		['name' => 'credential#deleteCredential', 'url' => '/api/v2/credentials/{credential_id}', 'verb' => 'DELETE'],

		//Revisions
		['name' => 'credential#getRevision', 'url' => '/api/v2/credentials/{credential_id}/revision', 'verb' => 'GET'],
		['name' => 'credential#deleteRevision', 'url' => '/api/v2/credentials/{credential_id}/revision/{revision_id}', 'verb' => 'DELETE'],

		//File stuff
		['name' => 'file#uploadFile', 'url' => '/api/v2/file', 'verb' => 'POST'],
		['name' => 'file#getFile', 'url' => '/api/v2/file/{file_id}', 'verb' => 'GET'],
		['name' => 'file#deleteFile', 'url' => '/api/v2/file/{file_id}', 'verb' => 'DELETE'],

		//Sharing stuff
		['name' => 'share#search', 'url' => '/api/v2/sharing/search', 'verb' => 'POST'],
		['name' => 'share#getVaultsByUser', 'url' => '/api/v2/sharing/vaults/{user_id}', 'verb' => 'GET'],
        ['name' => 'share#applyIntermediateShare', 'url' => '/api/v2/sharing/share', 'verb' => 'POST'],
        ['name' => 'share#savePendingRequest', 'url' => '/api/v2/sharing/save', 'verb' => 'POST'],
        ['name' => 'share#unshareCredential', 'url' => '/api/v2/sharing/unshare/{item_guid}', 'verb' => 'DELETE'],
        ['name' => 'share#getPendingRequests', 'url' => '/api/v2/sharing/pending', 'verb' => 'GET'],
        ['name' => 'share#deleteShareRequest', 'url' => '/api/v2/sharing/decline/{share_request_id}', 'verb' => 'DELETE'],
        ['name' => 'share#getVaultItems', 'url' => '/api/v2/sharing/vault/{vault_guid}/get', 'verb' => 'GET'],
        ['name' => 'share#getRevisions', 'url' => '/api/v2/sharing/revisions/{item_guid}', 'verb' => 'GET'],
        ['name' => 'share#getPublicCredentialData', 'url' => '/api/v2/sharing/public/credential/{credential_guid}', 'verb' => 'GET'],
        ['name' => 'share#createPublicShare', 'url' => '/api/v2/sharing/public', 'verb' => 'POST'],

		//Internal API
		['name' => 'internal#remind', 'url' => '/api/internal/notifications/remind/{credential_id}', 'verb' => 'POST'],
		['name' => 'internal#read', 'url' => '/api/internal/notifications/read/{credential_id}', 'verb' => 'DELETE'],
		['name' => 'internal#getAppVersion', 'url' => '/api/internal/version', 'verb' => 'GET'],
		['name' => 'internal#generatePerson', 'url' => '/api/internal/generate_person', 'verb' => 'GET'],

	]
];