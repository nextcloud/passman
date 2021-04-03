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

use OCA\Passman\Activity;
use OCA\Passman\Db\File;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\FileService;
use OCA\Passman\Service\NotificationService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Utility\NotFoundJSONResponse;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\Notification\IManager;


class ShareController extends ApiController {
	private $userId;
	private $activityService;
	private $groupManager;
	private $userManager;
	private $vaultService;
	private $shareService;
	private $credentialService;
	private $notificationService;
	private $fileService;
	private $settings;
	private $manager;

	private $limit = 50;
	private $offset = 0;

	public function __construct($AppName,
	                            IRequest $request,
	                            $UserId,
	                            IGroupManager $groupManager,
	                            IUserManager $userManager,
	                            ActivityService $activityService,
	                            VaultService $vaultService,
	                            ShareService $shareService,
	                            CredentialService $credentialService,
	                            NotificationService $notificationService,
	                            FileService $fileService,
	                            SettingsService $config,
	                            IManager $IManager
	) {
		parent::__construct(
			$AppName,
			$request,
			'GET, POST, DELETE, PUT, PATCH, OPTIONS',
			'Authorization, Content-Type, Accept',
			86400);

		$this->userId = $UserId;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->activityService = $activityService;
		$this->vaultService = $vaultService;
		$this->shareService = $shareService;
		$this->credentialService = $credentialService;
		$this->notificationService = $notificationService;
		$this->fileService = $fileService;
		$this->settings = $config;
		$this->manager = $IManager;
	}


	/**
	 * @param $item_id
	 * @param $item_guid
	 * @param $permissions
	 * @param $expire_timestamp
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function createPublicShare($item_id, $item_guid, $permissions, $expire_timestamp, $expire_views) {
		try {
			$credential = $this->credentialService->getCredentialByGUID($item_guid);
		} catch (\Exception $exception) {
			return new NotFoundResponse();
		}

		try {
			$acl = $this->shareService->getACL(null, $item_guid);
		} catch (\Exception $exception) {
			$acl = new SharingACL();
		}


		$acl->setItemId($item_id);
		$acl->setItemGuid($item_guid);
		$acl->setPermissions($permissions);
		$acl->setExpire($expire_timestamp);
		$acl->setExpireViews($expire_views);
		if (!$acl->getId()) {
			$this->shareService->createACLEntry($acl);

			$this->activityService->add(
				'item_shared_publicly', [$credential->getLabel()],
				'', array(),
				'', $this->userId->getUID(), Activity::TYPE_ITEM_SHARED);
		} else {
			$this->shareService->updateCredentialACL($acl);
		}

	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
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
			$shareRequests = $this->shareService->getPendingShareRequestsForCredential($item_guid, $first_vault['user_id']);
			if (count($shareRequests) > 0) {
				return new JSONResponse(array('error' => 'User got already pending requests'));
			}
		} catch (\Exception $exception) {
			// no need to catch this
		}

		$acl = null;
		try {
			$acl = $this->shareService->getCredentialAclForUser($first_vault['user_id'], $item_guid);
		} catch (\Exception $exception) {
			// no need to catch this
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

					$this->activityService->add(
						'item_shared', [$credential->getLabel(), $target_user],
						'', array(),
						'', $this->userId->getUID(), Activity::TYPE_ITEM_SHARED);


					$this->activityService->add(
						'item_share_received', [$credential->getLabel(), $this->userId->getUID()],
						'', array(),
						'', $target_user, Activity::TYPE_ITEM_SHARED);
				}
			}
		}


		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function searchUsers($search) {
		$users = array();
		$usersTmp = $this->userManager->searchDisplayName($search, $this->limit, $this->offset);

		foreach ($usersTmp as $user) {
			if ($this->userId->getUID() !== $user->getUID() && count($this->vaultService->getByUser($user->getUID())) >= 1) {
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
	 * @NoCSRFRequired
	 */
	public function unshareCredential($item_guid) {
		$this->shareService->unshareCredential($item_guid);
		return new JSONResponse(array('result' => true));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function unshareCredentialFromUser($item_guid, $user_id) {
		$acl = null;
		$sr = null;
		try {
			$acl = $this->shareService->getCredentialAclForUser($user_id, $item_guid);
		} catch (\Exception $e) {

		}
		try {
			$shareRequests = $this->shareService->getPendingShareRequestsForCredential($item_guid, $user_id);
			$sr = array_pop($shareRequests);
		} catch (\Exception $e) {
			// no need to catch this
		}

		if ($sr) {
			$this->shareService->cleanItemRequestsForUser($sr);
			$notification = $this->manager->createNotification();
			$notification->setApp('passman')
				->setObject('passman_share_request', $sr->getId())
				->setUser($user_id);
			$this->manager->markProcessed($notification);
		}
		if ($acl) {
			$this->shareService->deleteShareACL($acl);
		}
		return new JSONResponse(array('result' => true));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function search($search) {
		$user_search = $this->searchUsers($search);
		return new JSONResponse($user_search);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
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
	 * @NoCSRFRequired
	 */
	public function savePendingRequest($item_guid, $target_vault_guid, $final_shared_key) {
		try {
			$sr = $this->shareService->getRequestByGuid($item_guid, $target_vault_guid);
		} catch (\Exception $ex) {
			return new NotFoundResponse();
		}

		$notification = $this->manager->createNotification();
		$notification->setApp('passman')
			->setObject('passman_share_request', $sr->getId())
			->setUser($this->userId->getUID());
		$this->manager->markProcessed($notification);

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
	 * @NoCSRFRequired
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
		} catch (\Exception $ex) {
			return new NotFoundResponse();
		}
	}

	/**
	 * @param $item_guid
	 * @return JSONResponse
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getRevisions($item_guid) {
		try {
			return new JSONResponse($this->shareService->getItemHistory($this->userId, $item_guid));
		} catch (\Exception $ex) {
			return new NotFoundJSONResponse();
		}
	}

	/**
	 * Obtains the list of credentials shared with this vault
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getVaultItems($vault_guid) {
		try {
			return new JSONResponse($this->shareService->getSharedItems($this->userId->getUID(), $vault_guid));
		} catch (\Exception $ex) {
			return new NotFoundResponse();
		}
	}

	/**
	 * @param $share_request_id
	 * @return JSONResponse
	 * @NoAdminRequired
	 * @NoCSRFRequired
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


			$notification = $this->manager->createNotification();
			$notification->setApp('passman')
				->setObject('passman_share_request', $share_request_id)
				->setUser($this->userId->getUID());
			$this->manager->markProcessed($notification);

			$this->shareService->cleanItemRequestsForUser($sr);
			return new JSONResponse(array('result' => true));
		} catch (\Exception $ex) {
			return new NotFoundJSONResponse();
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

		if ($acl->getExpire() > 0 && Utils::getTime() > $acl->getExpire()) {
			return new NotFoundJSONResponse();
		}

		$views = $acl->getExpireViews();
		if ($views === 0) {
			return new NotFoundJSONResponse();
		} else if ($views !== -1) {
			$views--;
			$acl->setExpireViews($views);
			$this->shareService->updateCredentialACL($acl);
		}


		try {
			$credential = $this->shareService->getSharedItem(null, $credential_guid);
			return new JSONResponse($credential);
		} catch (\Exception $ex) {
			return new NotFoundJSONResponse();
		}
	}

	/**
	 * @param $item_guid
	 * @return JSONResponse|NotFoundResponse
	 * @throws \OCP\DB\Exception
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getItemAcl($item_guid) {
		$acl = $this->shareService->getCredentialAclList($item_guid);
		$pending = $this->shareService->getCredentialPendingAclList($item_guid);
		try {
			$credential = $this->credentialService->getCredentialByGUID($item_guid);
			if ($credential->getUserId() === $this->userId->getUID()) {
				foreach ($pending as &$item) {
					$item = $item->asACLJson();
				}
				$acl = array_merge($acl, $pending);
				return new JSONResponse($acl);
			} else {
				return new NotFoundResponse();
			}
		} catch (\Exception $ex) {
			return new JSONResponse(array());
		}
	}

	/**
	 * @param $item_guid
	 * @param $file_guid
	 * @return array|File|NotFoundJSONResponse
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getFile($item_guid, $file_guid) {
		try {
			$credential = $this->credentialService->getCredentialByGUID($item_guid);
		} catch (\Exception $e) {
			return new NotFoundJSONResponse();
		}
		$userId = ($this->userId) ? $this->userId->getUID() : null;
		$acl = $this->shareService->getACL($userId, $credential->getGuid());
		if (!$acl->hasPermission(SharingACL::FILES)) {
			return new NotFoundJSONResponse();
		} else {
			return $this->fileService->getFileByGuid($file_guid);
		}
	}

	/**
	 * @param $item_guid
	 * @param  $user_id
	 * @param $permission
	 * @return JSONResponse
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function updateSharedCredentialACL($item_guid, $user_id, $permission) {
		try {
			$credential = $this->credentialService->getCredentialByGUID($item_guid);
		} catch (\Exception $exception) {
			return new NotFoundJSONResponse();
		}
		if ($this->userId->getUID() === $credential->getUserId()) {
			$acl = null;
			try {
				$acl = $this->shareService->getACL($user_id, $item_guid);
				$acl->setPermissions($permission);
				return $this->shareService->updateCredentialACL($acl);
			} catch (\Exception $exception) {

			}

			if ($acl === null) {
				$this->shareService->updatePendingShareRequestsForCredential($item_guid, $user_id, $permission);
			}

		}
	}
}
