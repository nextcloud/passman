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

use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\VaultService;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCA\Passman\Service\CredentialService;
use \OCP\App;

class AdminController extends ApiController {
	private $userId;
	private $vaultService;
	private $credentialService;
	private $fileService;
	private $revisionService;
	private $config;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								VaultService $vaultService,
								CredentialService $credentialService,
								FileService $fileService,
								CredentialRevisionService $revisionService,
								IConfig $config
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);
		$this->userId = $UserId;
		$this->vaultService = $vaultService;
		$this->credentialService = $credentialService;
		$this->fileService = $fileService;
		$this->revisionService = $revisionService;
		$this->config = $config;
	}


	public function searchUser($term){
		$um = \OC::$server->getUserManager();
		$results = array();
		$searchResult = $um->search($term);
		foreach ($searchResult as $user){
			array_push($results, array(
				"value" => $user->getUID(),
				"label" => $user->getDisplayName() . ' ('. $user->getBackendClassName() .')',
			));
		}
		return new JSONResponse($results);
	}

	public function moveCredentials($source_account, $destination_account){
		$vaults = $this->vaultService->getByUser($source_account);
		foreach ($vaults as $vault) {
			$credentials = $this->credentialService->getCredentialsByVaultId($vault->getId(), $source_account);
			foreach($credentials as $credential){
				$revisions = $this->revisionService->getRevisions($credential->getId());
				foreach ($revisions as $revision){
					$r = new CredentialRevision();
					$r->setId($revision['revision_id']);
					$r->setGuid($revision['guid']);
					$r->setCredentialId($credential->getId());
					$r->setUserId($destination_account);
					$r->setCreated($revision['created']);
					$r->setCredentialData(base64_encode(json_encode($revision['credential_data'])));
					$r->setEditedBy($revision['edited_by']);
					$this->revisionService->updateRevision($r);
				}

				$c = $credential->jsonSerialize();
				$c['user_id'] = $destination_account;
				$this->credentialService->updateCredential($c, true);
			}
			$vault->setUserId($destination_account);
			$this->vaultService->updateVault($vault);
		}

		$files = $this->fileService->getFilesFromUser($source_account);
		foreach($files as $file){
			$file->setUserId($destination_account);
			$this->fileService->updateFile($file);
		}
		return new JSONResponse(array('success'=> true));
	}
}