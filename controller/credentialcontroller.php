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
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Utility\NotFoundJSONResponse;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Activity;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\ShareService;
use OCP\IUser;

class CredentialController extends ApiController {
	private $userId;
	private $credentialService;
	private $activityService;
	private $credentialRevisionService;
	private $sharingService;

	public function __construct($AppName,
								IRequest $request,
								IUser $UserId,
								CredentialService $credentialService,
								ActivityService $activityService,
								CredentialRevisionService $credentialRevisionService,
								ShareService $sharingService
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->credentialService = $credentialService;
		$this->activityService = $activityService;
		$this->credentialRevisionService = $credentialRevisionService;
		$this->sharingService = $sharingService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function createCredential($changed, $created,
									 $credential_id, $custom_fields, $delete_time,
									 $description, $email, $expire_time, $favicon, $files, $guid,
									 $hidden, $label, $otp, $password, $renew_interval,
									 $tags, $url, $username, $vault_id) {
		$credential = array(
			'credential_id' => $credential_id,
			'guid' => $guid,
			'user_id' => $this->userId,
			'vault_id' => $vault_id,
			'label' => $label,
			'description' => $description,
			'created' => $created,
			'changed' => $changed,
			'tags' => $tags,
			'email' => $email,
			'username' => $username,
			'password' => $password,
			'url' => $url,
			'favicon' => $favicon,
			'renew_interval' => $renew_interval,
			'expire_time' => $expire_time,
			'delete_time' => $delete_time,
			'files' => $files,
			'custom_fields' => $custom_fields,
			'otp' => $otp,
			'hidden' => $hidden,

		);
		$credential = $this->credentialService->createCredential($credential);
		$link = ''; // @TODO create direct link to credential
		if(!$credential->getHidden()) {
			$this->activityService->add(
				Activity::SUBJECT_ITEM_CREATED_SELF, array($label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		}
		return new JSONResponse($credential);
	}

	/**
	 * @NoAdminRequired
	 */
	public function getCredential($credential_id) {
		return new JSONResponse($this->credentialService->getCredentialById($credential_id, $this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function updateCredential($changed, $created,
									 $credential_id, $custom_fields, $delete_time,
									 $description, $email, $expire_time, $favicon, $files, $guid,
									 $hidden, $label, $otp, $password, $renew_interval,
									 $tags, $url, $username, $vault_id, $revision_created, $shared_key, $acl) {


		$storedCredential = $this->credentialService->getCredentialById($credential_id, $this->userId);

		$credential = array(
			'credential_id' => $credential_id,
			'guid' => $guid,
			'label' => $label,
			'description' => $description,
			'created' => $created,
			'changed' => $changed,
			'vault_id' => $vault_id,
			'tags' => $tags,
			'email' => $email,
			'username' => $username,
			'password' => $password,
			'url' => $url,
			'favicon' => $favicon,
			'renew_interval' => $renew_interval,
			'expire_time' => $expire_time,
			'files' => $files,
			'custom_fields' => $custom_fields,
			'delete_time' => $delete_time,
			'hidden' => $hidden,
			'otp' => $otp,
			'shared_key' => ($shared_key === null) ? '' : $shared_key,
		);


		if ($storedCredential->getUserId() !== $this->userId) {
			$acl = $this->sharingService->getCredentialAclForUser($this->userId, $storedCredential->getGuid());
			if ($acl->hasPermission(SharingACL::WRITE)) {
				$credential['shared_key'] = $storedCredential->getSharedKey();
			} else {
				return new DataResponse(['msg' => 'Not authorized'], Http::STATUS_UNAUTHORIZED);
			}
		}
		$link = ''; // @TODO create direct link to credential
		$activity = false;
		if ($revision_created) {
			$activity = 'item_apply_revision';
			$this->activityService->add(
				$activity . '_self', array($label, $this->userId, $revision_created),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		} else if (($storedCredential->getDeleteTime() == 0) && $delete_time > 0) {
			$activity = 'item_deleted';
			$this->activityService->add(
				$activity . '_self', array($label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		} else if (($storedCredential->getDeleteTime() > 0) && $delete_time == 0) {
			$activity = 'item_recovered';
			$this->activityService->add(
				$activity . '_self', array($label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		} else if ($label != $storedCredential->getLabel()) {
			$activity = 'item_renamed';
			$this->activityService->add(
				$activity . '_self', array($storedCredential->getLabel(), $label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_RENAMED);
		} else {
			$activity = 'item_edited';
			$this->activityService->add(
				$activity . '_self', array($label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		}
		$acl_list = null;

		try {
			$acl_list = $this->sharingService->getCredentialAclList($storedCredential->getGuid());
		} catch (DoesNotExistException $exception) {

		}
		if ($acl_list) {
			$params = array();
			switch ($activity) {
				case 'item_recovered':
				case 'item_deleted':
				case 'item_edited':
					$params = array($credential['label'], $this->userId);
					break;
				case 'item_apply_revision':
					$params = array($credential['label'], $this->userId, $revision_created);
					break;
				case 'item_renamed':
					$params = array($storedCredential->getLabel(), $label, $this->userId);
					break;
			}

			foreach ($acl_list as $sharingACL) {
				$target_user = $sharingACL->getUserId();
				if($target_user == $this->userId){
					continue;
				}
				$this->activityService->add(
					$activity, $params,
					'', array(),
					$link, $target_user, Activity::TYPE_ITEM_ACTION);
			}
			if ($this->userId != $storedCredential->getUserId()) {
				$this->activityService->add(
					$activity, $params,
					'', array(),
					$link, $storedCredential->getUserId(), Activity::TYPE_ITEM_ACTION);
			}
		}

		$this->credentialRevisionService->createRevision($storedCredential, $storedCredential->getUserId(), $credential_id, $this->userId);
		$credential = $this->credentialService->updateCredential($credential);

		return new JSONResponse($credential);
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteCredential($credential_id) {
		$credential = $this->credentialService->getCredentialById($credential_id, $this->userId);
		if ($credential) {
			$result = $this->credentialService->deleteCredential($credential);
			$this->activityService->add(
				'item_destroyed_self', array($credential->getLabel()),
				'', array(),
				'', $this->userId, Activity::TYPE_ITEM_ACTION);
		} else {
			$result = false;
		}
		return new JSONResponse($result);
	}


	/**
	 * @NoAdminRequired
	 */
	public function getRevision($credential_guid) {
	    try {
            $credential = $this->credentialService->getCredentialByGUID($credential_guid);
        }
        catch (DoesNotExistException $ex){
            return new NotFoundJSONResponse();
        }

        // If the request was made by the owner of the credential
        if ($this->userId->getUID() == $credential->getUserId()) {
            $result = $this->credentialRevisionService->getRevisions($credential->getId(), $this->userId);
        }
        else {
            $acl = $this->sharingService->getACL($this->userId->getUID(), $credential_guid);
            if ($acl->hasPermission(SharingACL::HISTORY)){
                $result = $this->credentialRevisionService->getRevisions($credential->getId());
            }
            else {
                return new NotFoundJSONResponse();
            }
        }

		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteRevision($credential_id, $revision_id) {
		$result = $this->credentialRevisionService->deleteRevision($revision_id, $this->userId);
		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 */
	public function updateRevision($credential_id, $revision_id, $credential_data){
		$revision = null;
		try {
			$credential = $this->credentialService->getCredentialById($credential_id, $this->userId);
		} catch (DoesNotExistException $e) {
			return new NotFoundResponse();
		}

		try{
			$revision = $this->credentialRevisionService->getRevision($revision_id);
		} catch(DoesNotExistException $exception){
			return new NotFoundResponse();
		}

		$revision->setCredentialData($credential_data);

		$this->credentialRevisionService->updateRevision($revision);
		return new JSONResponse(array());
	}
}