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

use OCA\Files_External\NotFoundException;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\Vault;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\NotificationService;
use OCA\Passman\Service\ShareService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\NotFoundResponse;
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
	private $credentialService;
	private $notificationService;

	private $limit = 50;
	private $offset = 0;

	public function __construct($AppName,
								IRequest $request,
								IUser $UserId,
								IGroupManager $groupManager,
								IUserManager $userManager,
								ActivityService $activityService,
								VaultService $vaultService,
								ShareService $shareService,
								CredentialService $credentialService,
								NotificationService $notificationService
	) {
		parent::__construct($AppName, $request);

		$this->userId = $UserId;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->activityService = $activityService;
		$this->vaultService = $vaultService;
		$this->shareService = $shareService;
		$this->credentialService = $credentialService;
		$this->notificationService = $notificationService;
	}

	/**
	 * @param $item_id
	 * @param $item_guid
	 * @param $permissions
	 * @param $expire_timestamp
	 * @NoAdminRequired
	 */
	public function createPublicShare($item_id, $item_guid, $permissions, $expire_timestamp, $expire_views) {

		try {
			$acl = $this->shareService->getACL(null, $item_guid);
		} catch (DoesNotExistException $exception) {
			$acl = new SharingACL();
		}


		$acl->setItemId($item_id);
		$acl->setItemGuid($item_guid);
		$acl->setPermissions($permissions);
		$acl->setExpire($expire_timestamp);
		$acl->setExpireViews($expire_views);
		if (!$acl->getId()) {
			$this->shareService->createACLEntry($acl);
		} else {
			$this->shareService->updateCredentialACL($acl);
		}

	}

	/**
	 * @NoAdminRequired
	 */
	public function applyIntermediateShare($item_id, $item_guid, $vaults, $permissions) {
		/**
		 * Assemble notification
		 */
		//@TODO add expire_time
		//@TODO add expire_views
		$credential = $this->credentialService->getCredentialById($item_id, $this->userId->getUID());
		$credential_owner = $credential->getUserId();

		$first_vault = $vaults[0];
		try {
			$shareRequests = $this->shareService->getPendingShareRequests($item_guid, $first_vault['user_id']);
			if (count($shareRequests) > 0) {
				return new JSONResponse(array('error' => 'User got already pending requests'));
			}
		} catch (DoesNotExistException $exception) {

		}

		$acl = null;
		try {
			$acl = $this->shareService->getCredentialAclForUser($first_vault['user_id'], $item_guid);
		} catch (DoesNotExistException $exception) {

		}

		if ($acl) {
			return new JSONResponse(array('error' => 'User got already this credential'));
		}

		$result = $this->shareService->createBulkRequests($item_id, $item_guid, $vaults, $permissions, $credential_owner);
		if ($credential) {
			$processed_users = array();
			foreach ($result as $vault) {
				if (!in_array($vault->getTargetUserId(), $processed_users)) {
					$target_user = $vault->getTargetUserId();
					$notification = array(
						'from_user' => ucfirst($this->userId->getDisplayName()),
						'credential_label' => $credential->getLabel(),
						'credential_id' => $credential->getId(),
						'item_id' => $credential->getId(),
						'target_user' => $target_user,
						'req_id' => $vault->getId()
					);
					$this->notificationService->credentialSharedNotification(
						$notification
					);
					array_push($processed_users, $target_user);
				}
			}
		}
		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 */
	public function searchUsers($search) {
		$users = array();
		$usersTmp = $this->userManager->searchDisplayName($search, $this->limit, $this->offset);

		foreach ($usersTmp as $user) {
			if ($this->userId->getUID() != $user->getUID() && count($this->vaultService->getByUser($user->getUID())) >= 1) {
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
	public function unshareCredential($item_guid) {
		$acl_list = $this->shareService->getCredentialAclList($item_guid);
		$request_list = $this->shareService->getShareRequestsByGuid($item_guid);
		foreach ($acl_list as $ACL) {
			$this->shareService->deleteShareACL($ACL);
		}
		foreach ($request_list as $request) {
			$this->shareService->deleteShareRequest($request);
		}
		return new JSONResponse(array('result' => true));
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
	public function getVaultsByUser($user_id) {
		$user_vaults = $this->vaultService->getByUser($user_id);
		$result = array();
		foreach ($user_vaults as $vault) {
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

	/**
	 * @NoAdminRequired
	 */
	public function savePendingRequest($item_guid, $target_vault_guid, $final_shared_key) {
		try {
			$sr = $this->shareService->getRequestByGuid($item_guid, $target_vault_guid);
		} catch (DoesNotExistException $ex) {
			return new NotFoundResponse();
		}

		$manager = \OC::$server->getNotificationManager();
		$notification = $manager->createNotification();
		$notification->setApp('passman')
			->setObject('passman_share_request', $sr->getId())
			->setUser($this->userId->getUID());
		$manager->markProcessed($notification);

		$notification = array(
			'from_user' => ucfirst($this->userId->getDisplayName()),
			'credential_label' => $this->credentialService->getCredentialLabelById($sr->getItemId())->getLabel(),
			'target_user' => $sr->getFromUserId(),
			'req_id' => $sr->getId()
		);

		$this->notificationService->credentialAcceptedSharedNotification(
			$notification
		);


		$this->shareService->applyShare($item_guid, $target_vault_guid, $final_shared_key);
	}

	/**
	 * @NoAdminRequired
	 */
	public function getPendingRequests() {
		try {
			$requests = $this->shareService->getUserPendingRequests($this->userId->getUID());
			$results = array();
			foreach ($requests as $request) {
				$result = $request->jsonSerialize();
				$c = $this->credentialService->getCredentialLabelById($request->getItemId());
				$result['credential_label'] = $c->getLabel();
				array_push($results, $result);
			}
			return new JSONResponse($results);
		} catch (DoesNotExistException $ex) {
			return new NotFoundResponse();
		}
	}

	/**
	 * @param $item_guid
	 * @return JSONResponse
	 * @NoAdminRequired
	 */
	public function getRevisions($item_guid) {
		try {
			return new JSONResponse($this->shareService->getItemHistory($this->userId, $item_guid));
		} catch (DoesNotExistException $ex) {
			return new NotFoundResponse();
		}
	}

	/**
	 * Obtains the list of credentials shared with this vault
	 *
	 * @NoAdminRequired
	 */
	public function getVaultItems($vault_guid) {
		try {
			return new JSONResponse($this->shareService->getSharedItems($this->userId->getUID(), $vault_guid));
		} catch (DoesNotExistException $ex) {
			return new NotFoundResponse();
		}
	}

	/**
	 * @param $share_request_id
	 * @return JSONResponse
	 * @NoAdminRequired
	 */
	public function deleteShareRequest($share_request_id) {
		try {

			$sr = $this->shareService->getShareRequestById($share_request_id);
			$notification = array(
				'from_user' => ucfirst($this->userId->getDisplayName()),
				'credential_label' => $this->credentialService->getCredentialLabelById($sr->getItemId())->getLabel(),
				'target_user' => $sr->getFromUserId(),
				'req_id' => $sr->getId()
			);
			$this->notificationService->credentialDeclinedSharedNotification(
				$notification
			);


			$manager = \OC::$server->getNotificationManager();
			$notification = $manager->createNotification();
			$notification->setApp('passman')
				->setObject('passman_share_request', $share_request_id)
				->setUser($this->userId->getUID());
			$manager->markProcessed($notification);

			$this->shareService->cleanItemRequestsForUser($sr);
			return new JSONResponse(array('result' => true));
		} catch (DoesNotExistException $ex) {
			return new NotFoundResponse();
		}
	}

	/**
	 * @param $credential_guid
	 * @return JSONResponse
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function getPublicCredentialData($credential_guid) {

		//@TODO Check expire date
		$acl = $this->shareService->getACL(null, $credential_guid);
		$views = $acl->getExpireViews();
		if ($views === 0) {
			return new NotFoundResponse();
		} else if ($views != -1) {
			$views--;
			$acl->setExpireViews($views);
			$this->shareService->updateCredentialACL($acl);
		}

		if ($acl->getExpire() > 0 && time() > $acl->getExpire()) {
			return new NotFoundResponse();
		}

		try {
			$credential = $this->shareService->getSharedItem(null, $credential_guid);
			return new JSONResponse($credential);
		} catch (DoesNotExistException $ex) {
			return new NotFoundResponse();
		}
	}

	public function getItemAcl($item_guid) {
		$acl = $this->shareService->getCredentialAclList($item_guid);
		$pending = $this->shareService->getCredentialPendingAclList($item_guid);
		try {
			$credential = $this->credentialService->getCredentialByGUID($item_guid);
			if ($credential->getUserId() == $this->userId->getUID()) {
				foreach ($pending as &$item) {
					$item = $item->asACLJson();
				}
				$acl = array_merge($acl, $pending);
				return new JSONResponse($acl);
			} else {
				return new NotFoundResponse();
			}
		} catch (DoesNotExistException $ex) {
			return new JSONResponse(array());
		}
	}

	public function updateSharedCredentialACL($item_guid, $user_id, $permission) {
		try {
			$credential = $this->credentialService->getCredentialByGUID($item_guid);
		} catch (DoesNotExistException $exception) {
			return new NotFoundResponse();
		}
		if ($this->userId->getUID() == $credential->getUserId()) {
			$acl = null;
			try {
				$acl = $this->shareService->getACL($user_id, $item_guid);
				$acl->setPermissions($permission);
				return $this->shareService->updateCredentialACL($acl);
			} catch (DoesNotExistException $exception) {

			}
			
			if ($acl === null) {
				$sr = $this->shareService->getPendingShareRequestsForCredential($item_guid, $user_id);
				foreach ($sr as $shareRequest) {
					$shareRequest->setPermissions($permission);
					$this->shareService->updateCredentialShareRequest($shareRequest);
				}
			}

		}
	}
}