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
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Activity;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialRevisionService;

class CredentialController extends ApiController {
	private $userId;
	private $credentialService;
	private $activityService;
	private $credentialRevisionService;

	public function __construct($AppName,
								IRequest $request,
								$UserId,
								CredentialService $credentialService,
								ActivityService $activityService,
								CredentialRevisionService $credentialRevisionService) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->credentialService = $credentialService;
		$this->activityService = $activityService;
		$this->credentialRevisionService = $credentialRevisionService;
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
		$this->activityService->add(
			Activity::SUBJECT_ITEM_CREATED_SELF, array($label, $this->userId),
			'', array(),
			$link, $this->userId, Activity::TYPE_ITEM_ACTION);
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
									 $tags, $url, $username, $vault_id, $revision_created, $shared_key) {
		$credential = array(
			'credential_id' => $credential_id,
			'guid' => $guid,
			'user_id' => $this->userId,
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
			'shared_key' => ($shared_key) ? $shared_key : null,
		);


		$storedCredential = $this->credentialService->getCredentialById($credential_id, $this->userId);

		$link = ''; // @TODO create direct link to credential
		if ($revision_created) {
			$this->activityService->add(
				'item_apply_revision_self', array($label, $this->userId, $revision_created),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		} else if (($storedCredential->getDeleteTime() == 0) && $delete_time > 0) {
			$this->activityService->add(
				'item_deleted_self', array($label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		} else if (($storedCredential->getDeleteTime() > 0) && $delete_time == 0) {
			$this->activityService->add(
				'item_recovered_self', array($label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		} else if ($label != $storedCredential->getLabel()) {
			$this->activityService->add(
				'item_renamed_self', array($storedCredential->getLabel(), $label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_RENAMED);
		} else {
			$this->activityService->add(
				'item_edited_self', array($label, $this->userId),
				'', array(),
				$link, $this->userId, Activity::TYPE_ITEM_ACTION);
		}

		$this->credentialRevisionService->createRevision($storedCredential, $this->userId, $credential_id);
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
	public function getRevision($credential_id) {
		$result = $this->credentialRevisionService->getRevisions($credential_id, $this->userId);
		return new JSONResponse($result);
	}

	/**
	 * @NoAdminRequired
	 */
	public function deleteRevision($credential_id, $revision_id) {
		$result = $this->credentialRevisionService->deleteRevision($revision_id, $this->userId);
		return new JSONResponse($result);
	}
}