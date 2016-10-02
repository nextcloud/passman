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

use OCA\Passman\Db\Vault;
use OCA\Passman\Service\ShareService;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;

use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUserManager;
use OCP\IUser;

use OCA\Passman\Service\VaultService;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Activity;



class ShareController extends ApiController {
	private $userId;
	private $activityService;
	private $groupManager;
	private $userManager;
	private $vaultService;
    private $shareService;

	private $limit = 50;
	private $offset = 0;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								IGroupManager $groupManager,
								IUserManager $userManager,
								ActivityService $activityService,
								VaultService $vaultService,
                                ShareService $shareService
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->activityService = $activityService;
		$this->vaultService = $vaultService;
        $this->shareService = $shareService;
	}

    public function applyIntermediateShare($item_id, $item_guid, $vaults, $permissions){
        return new JSONResponse($this->shareService->createBulkRequests($item_id, $item_guid, $vaults, $permissions));
    }

	public function searchUsers($search) {
		$users = array();
		$usersTmp = $this->userManager->searchDisplayName($search, $this->limit, $this->offset);

		foreach ($usersTmp as $user) {
			if($this->userId != $user->getUID() && count($this->vaultService->getByUser($user->getUID())) >= 1) {
				$users[] = array(
					'text' => $user->getDisplayName(),
					'uid' => $user->getUID(),
					'type' => 'user'
				);
			}
		}
		return $users;
	}


	/**
	 * @NoAdminRequired
	 */
	public function search($search) {
		$user_search = $this->searchUsers($search);
		return new JSONResponse($user_search);
	}


	/**
	 * @NoAdminRequired
	 */
	public function getVaultsByUser($user_id){
		$user_vaults = $this->vaultService->getByUser($user_id);
		$result = array();
		foreach($user_vaults as $vault){
			array_push($result,
				array(
					'vault_id' => $vault->getId(),
					'guid' => $vault->getGuid(),
					'public_sharing_key' => $vault->getPublicSharingKey(),
					'user_id' => $user_id,
				));
		}
		return new JSONResponse($result);
	}

	public function share($credential){

		$link = '';
		$this->activityService->add(
			'item_shared', array($credential->label, $this->userId),
			'', array(),
			$link, $this->userId, Activity::TYPE_ITEM_ACTION);
	}

}