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
		['name' => 'Page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'Page#bookmarklet', 'url' => '/bookmarklet', 'verb' => 'GET'],
		['name' => 'Page#publicSharePage', 'url' => '/share/public', 'verb' => 'GET'],

		//Vault
		['name' => 'Vault#listVaults', 'url' => '/api/v2/vaults', 'verb' => 'GET'],
		['name' => 'Vault#create', 'url' => '/api/v2/vaults', 'verb' => 'POST'],
		['name' => 'Vault#get', 'url' => '/api/v2/vaults/{vault_guid}', 'verb' => 'GET'],
		['name' => 'Vault#update', 'url' => '/api/v2/vaults/{vault_guid}', 'verb' => 'PATCH'],
		['name' => 'Vault#delete', 'url' => '/api/v2/vaults/{vault_guid}', 'verb' => 'DELETE'],
		//@TODO make frontend use PATCH
		['name' => 'Vault#updateSharingKeys', 'url' => '/api/v2/vaults/{vault_guid}/sharing-keys', 'verb' => 'POST'],

		//Credential
		['name' => 'Credential#createCredential', 'url' => '/api/v2/credentials', 'verb' => 'POST'],
		['name' => 'Credential#getCredential', 'url' => '/api/v2/credentials/{credential_guid}', 'verb' => 'GET'],
		['name' => 'Credential#updateCredential', 'url' => '/api/v2/credentials/{credential_guid}', 'verb' => 'PATCH'],
		['name' => 'Credential#deleteCredential', 'url' => '/api/v2/credentials/{credential_guid}', 'verb' => 'DELETE'],

		//Revisions
		['name' => 'Credential#getRevision', 'url' => '/api/v2/credentials/{credential_guid}/revision', 'verb' => 'GET'],
		['name' => 'Credential#deleteRevision', 'url' => '/api/v2/credentials/{credential_guid}/revision/{revision_id}', 'verb' => 'DELETE'],
		['name' => 'Credential#updateRevision', 'url' => '/api/v2/credentials/{credential_guid}/revision/{revision_id}', 'verb' => 'PATCH'],

		//File stuff
		['name' => 'File#uploadFile', 'url' => '/api/v2/file', 'verb' => 'POST'],
		['name' => 'File#getFile', 'url' => '/api/v2/file/{file_id}', 'verb' => 'GET'],
		['name' => 'File#deleteFile', 'url' => '/api/v2/file/{file_id}', 'verb' => 'DELETE'],
		['name' => 'File#deleteFiles', 'url' => '/api/v2/files/delete', 'verb' => 'POST'],
		['name' => 'File#updateFile', 'url' => '/api/v2/file/{file_id}', 'verb' => 'PATCH'],

		//Sharing stuff
		['name' => 'Share#search', 'url' => '/api/v2/sharing/search', 'verb' => 'POST'],
		['name' => 'Share#getVaultsByUser', 'url' => '/api/v2/sharing/vaults/{user_id}', 'verb' => 'GET'],
        ['name' => 'Share#applyIntermediateShare', 'url' => '/api/v2/sharing/share', 'verb' => 'POST'],
        ['name' => 'Share#savePendingRequest', 'url' => '/api/v2/sharing/save', 'verb' => 'POST'],
        ['name' => 'Share#getPendingRequests', 'url' => '/api/v2/sharing/pending', 'verb' => 'GET'],
        ['name' => 'Share#deleteShareRequest', 'url' => '/api/v2/sharing/decline/{share_request_id}', 'verb' => 'DELETE'],
        ['name' => 'Share#getVaultItems', 'url' => '/api/v2/sharing/vault/{vault_guid}/get', 'verb' => 'GET'],
        ['name' => 'Share#getVaultAclEntries', 'url' => '/api/v2/sharing/vault/{vault_guid}/acl', 'verb' => 'GET'],
        ['name' => 'Share#createPublicShare', 'url' => '/api/v2/sharing/public', 'verb' => 'POST'],
        ['name' => 'Share#getPublicCredentialData', 'url' => '/api/v2/sharing/credential/{credential_guid}/public', 'verb' => 'GET'],
        ['name' => 'Share#unshareCredential', 'url' => '/api/v2/sharing/credential/{item_guid}', 'verb' => 'DELETE'],
        ['name' => 'Share#unshareCredentialFromUser', 'url' => '/api/v2/sharing/credential/{item_guid}/{user_id}', 'verb' => 'DELETE'],
        ['name' => 'Share#getRevisions', 'url' => '/api/v2/sharing/credential/{item_guid}/revisions', 'verb' => 'GET'],
        ['name' => 'Share#getItemAcl', 'url' => '/api/v2/sharing/credential/{item_guid}/acl', 'verb' => 'GET'],
		['name' => 'Share#uploadFile', 'url' => '/api/v2/sharing/credential/{item_guid}/file', 'verb' => 'POST'],
        ['name' => 'Share#getFile', 'url' => '/api/v2/sharing/credential/{item_guid}/file/{file_guid}', 'verb' => 'GET'],
        ['name' => 'Share#updateSharedCredentialACL', 'url' => '/api/v2/sharing/credential/{item_guid}/acl', 'verb' => 'PATCH'],
        ['name' => 'Share#updateSharedCredentialACLSharedKey', 'url' => '/api/v2/sharing/credential/{item_guid}/acl/shared_key', 'verb' => 'PATCH'],
		['name' => 'Internal#getAppVersion', 'url' => '/api/v2/version', 'verb' => 'GET'],

		//Settings
		['name' => 'Settings#getSettings', 'url' => '/api/v2/settings', 'verb' => 'GET'],
		['name' => 'Settings#saveUserSetting', 'url' => '/api/v2/settings/{key}/{value}', 'verb' => 'POST'],
		['name' => 'Settings#saveAdminSetting', 'url' => '/api/v2/settings/{key}/{value}/admin1/admin2', 'verb' => 'POST'],


		//Translations
		['name' => 'Translation#getLanguageStrings', 'url' => '/api/v2/language', 'verb' => 'GET'],


		#Icons
		['name' => 'Icon#getSingleIcon', 'url' => '/api/v2/geticon/{base64Url}', 'verb' => 'GET'],
		['name' => 'Icon#getIcon', 'url' => '/api/v2/icon/{base64Url}', 'verb' => 'GET'],
		['name' => 'Icon#getIcon', 'url' => '/api/v2/icon/{base64Url}/{credentialId}', 'verb' => 'GET'],
		['name' => 'Icon#getLocalIconList', 'url' => '/api/v2/icon/list', 'verb' => 'GET'],

		//
		['name' => 'Vault#preflighted_cors', 'url' => '/api/v2/{path}', 'verb' => 'OPTIONS', 'requirements' => array('path' => '.+')],
		//Internal API
		['name' => 'Internal#remind', 'url' => '/api/internal/notifications/remind/{credential_id}', 'verb' => 'POST'],
		['name' => 'Internal#read', 'url' => '/api/internal/notifications/read/{credential_id}', 'verb' => 'DELETE'],
		['name' => 'Internal#getAppVersion', 'url' => '/api/internal/version', 'verb' => 'GET'],
		['name' => 'Internal#generatePerson', 'url' => '/api/internal/generate_person', 'verb' => 'GET'],

		//Admin routes
		['name' => 'Admin#searchUser', 'url' => '/admin/search', 'verb' => 'GET'],
		['name' => 'Admin#moveCredentials', 'url' => '/admin/move', 'verb' => 'POST'],
		['name' => 'Admin#requestDeletion', 'url' => '/admin/request-deletion/{vault_guid}', 'verb' => 'POST'],
		['name' => 'Admin#deleteRequestDeletion', 'url' => '/admin/request-deletion/{vault_guid}', 'verb' => 'DELETE'],
		['name' => 'Admin#listRequests', 'url' => '/admin/delete-requests', 'verb' => 'GET'],
		['name' => 'Admin#acceptRequestDeletion', 'url' => '/admin/accept-delete-request', 'verb' => 'POST'],
	]
];
