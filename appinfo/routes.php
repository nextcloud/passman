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

		//Get vaults
		['name' => 'vault#index', 'url' => '/api/v1/vaults', 'verb' => 'GET'],

		//Vault
		['name' => 'vault#create', 'url' => '/api/v1/vaults', 'verb' => 'POST'],
		['name' => 'vault#get', 'url' => '/api/v1/vaults/{vault_id}', 'verb' => 'GET'],
		['name' => 'vault#update', 'url' => '/api/v1/vaults/{vault_id}', 'verb' => 'PATCH'],
		['name' => 'vault#delete', 'url' => '/api/v1/vaults/{vault_id}', 'verb' => 'DELETE'],

		//Credential
		['name' => 'credential#create', 'url' => '/api/v1/credentials', 'verb' => 'POST'],
		['name' => 'credential#get', 'url' => '/api/v1/credentials/{credential_id}', 'verb' => 'GET'],
		['name' => 'credential#update', 'url' => '/api/v1/credentials/{credential_id}', 'verb' => 'PATCH'],
		['name' => 'credential#delete', 'url' => '/api/v1/credentials/{credential_id}', 'verb' => 'DELETE'],

		//Revisions
		['name' => 'revision#get', 'url' => '/api/v1/credentials/{credential_id}/revision', 'verb' => 'GET'],
		['name' => 'revision#delete', 'url' => '/api/v1/credentials/{credential_id}/revision/{revision_id}', 'verb' => 'DELETE'],

		//File stuff
		['name' => 'file#upload', 'url' => '/api/v1/credentials/{credential_id}/file', 'verb' => 'POST'],
		['name' => 'file#delete', 'url' => '/api/v1/credentials/{credential_id}/file/{file_id}', 'verb' => 'DELETE'],

	]
];