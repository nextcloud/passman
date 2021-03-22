<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Passman\Service;


use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\Exception;
use OCP\DB\IResult;
use OCP\Notification\IManager;

class ShareService {
	private SharingACLMapper $sharingACL;
	private ShareRequestMapper $shareRequest;
	private CredentialMapper $credential;
	private CredentialRevisionService $revisions;
	private EncryptService $encryptService;
	private IManager $IManager;


	public function __construct(
		SharingACLMapper $sharingACL,
		ShareRequestMapper $shareRequest,
		CredentialMapper $credentials,
		CredentialRevisionService $revisions,
		EncryptService $encryptService,
		IManager $IManager
	) {
		$this->sharingACL = $sharingACL;
		$this->shareRequest = $shareRequest;
		$this->credential = $credentials;
		$this->revisions = $revisions;
		$this->encryptService = $encryptService;
		$this->IManager = $IManager;
	}

	/**
	 * Creates requests for all the items on the request array of objects.
	 * This array must follow this spec:
	 *      user_id:    The target user id
	 *      vault_id:   The id of the target vault
	 *      guid:       The guid of the target vault
	 *      key:        The shared key cyphered with the target vault RSA public key
	 *
	 * @param $target_item_id   string      The shared item ID
	 * @param $target_item_guid string      The shared item GUID
	 * @param $request_array    array
	 * @param $permissions      integer     Must be created with a bitmask from options on the ShareRequest class
	 * @return array                        Array of sharing requests
	 */
	public function createBulkRequests($target_item_id, $target_item_guid, $request_array, $permissions, $credential_owner) {
		$created = Utils::getTime();
		$requests = array();
		foreach ($request_array as $req) {
			$t = new ShareRequest();
			$t->setItemId($target_item_id);
			$t->setItemGuid($target_item_guid);
			$t->setTargetUserId($req['user_id']);
			$t->setTargetVaultId($req['vault_id']);
			$t->setTargetVaultGuid($req['guid']);
			$t->setSharedKey($req['key']);
			$t->setPermissions($permissions);
			$t->setCreated($created);
			$t->setFromUserId($credential_owner);
			array_push($requests, $this->shareRequest->createRequest($t));
		}
		return $requests;
	}

	/**
	 * @param SharingACL $acl
	 * @return Entity
	 */
	public function createACLEntry(SharingACL $acl) {
		if ($acl->getCreated() === null) $acl->setCreated((new \DateTime())->getTimestamp());
		return $this->sharingACL->createACLEntry($acl);
	}

	/**
	 * Applies the given share, defaults to no expire
	 *
	 * @param string $item_guid
	 * @param string $target_vault_guid
	 * @param string $final_shared_key
	 * @throws DoesNotExistException
	 * @throws Exception
	 * @throws MultipleObjectsReturnedException
	 */
	public function applyShare(string $item_guid, string $target_vault_guid, string $final_shared_key) {
		$request = $this->shareRequest->getRequestByItemAndVaultGuid($item_guid, $target_vault_guid);
		$permissions = $request->getPermissions();

		$acl = new SharingACL();
		$acl->setItemId($request->getItemId());
		$acl->setItemGuid($request->getItemGuid());
		$acl->setUserId($request->getTargetUserId());
		$acl->setCreated($request->getCreated());
		$acl->setExpire(0);
		$acl->setPermissions($permissions);
		$acl->setVaultId($request->getTargetVaultId());
		$acl->setVaultGuid($request->getTargetVaultGuid());
		$acl->setSharedKey($final_shared_key);

		$this->sharingACL->createACLEntry($acl);
		$this->shareRequest->cleanItemRequestsForUser($request->getItemId(), $request->getTargetUserId());
	}

	/**
	 * Obtains pending requests for the given user ID
	 *
	 * @param string $user_id
	 * @return Entity[]
	 */
	public function getUserPendingRequests(string $user_id) {
		return $this->shareRequest->getUserPendingRequests($user_id);
	}

	/**
	 * Get shared credentials from a user
	 *
	 * @param string $user_id
	 * @param string $vault_guid
	 * @return array
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getSharedItems(string $user_id, string $vault_guid) {
		$entries = $this->sharingACL->getVaultEntries($user_id, $vault_guid);

		$return = [];
		foreach ($entries as $entry) {
			// Check if the user can read the credential, probably unnecesary, but just to be sure
			if (!$entry->hasPermission(SharingACL::READ)) continue;
			$tmp = $entry->jsonSerialize();
			$credential = $this->credential->getCredentialById($entry->getItemId());
			$credential = $this->encryptService->decryptCredential($credential);
			$tmp['credential_data'] = $credential->jsonSerialize();

			if (!$entry->hasPermission(SharingACL::FILES)) unset($tmp['credential_data']['files']);
			unset($tmp['credential_data']['shared_key']);
			$return[] = $tmp;
		}
		return $return;
	}

	/**
	 * Gets the acl for a given item guid
	 *
	 * @param string $user_id
	 * @param string $item_guid
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getACL(string $user_id, string $item_guid) {
		return $this->sharingACL->getItemACL($user_id, $item_guid);
	}

	/**
	 * @param string $user_id
	 * @param string $item_guid
	 * @return array|mixed
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getSharedItem(string $user_id, string $item_guid) {
		$acl = $this->sharingACL->getItemACL($user_id, $item_guid);

		// Check if the user can read the credential, probably unnecesary, but just to be sure
		if (!$acl->hasPermission(SharingACL::READ)) throw new DoesNotExistException("Item not found or wrong access level");

		$tmp = $acl->jsonSerialize();
		$credential = $this->credential->getCredentialById($acl->getItemId());
		$credential = $this->encryptService->decryptCredential($credential);

		$tmp['credential_data'] = $credential->jsonSerialize();

		if (!$acl->hasPermission(SharingACL::FILES)) unset($tmp['credential_data']['files']);
		unset($tmp['credential_data']['shared_key']);

		return $tmp;
	}

	/**
	 * Gets history from the given item checking the user's permissions to access it
	 *
	 * @param string $user_id
	 * @param string $item_guid
	 * @return array|Entity[]
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws \Exception
	 */
	public function getItemHistory(string $user_id, string $item_guid) {
		$acl = $this->sharingACL->getItemACL($user_id, $item_guid);
		if (!$acl->hasPermission(SharingACL::READ | SharingACL::HISTORY)) return [];

		return $this->revisions->getRevisions($acl->getItemId());
	}


	/**
	 * Deletes a share request by the item ID
	 *
	 * @param ShareRequest $request
	 * @return int|IResult
	 * @throws Exception
	 */
	public function cleanItemRequestsForUser(ShareRequest $request) {
		return $this->shareRequest->cleanItemRequestsForUser($request->getItemId(), $request->getTargetUserId());
	}

	/**
	 * Get an share request by id
	 *
	 * @param int $id
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getShareRequestById(int $id) {
		return $this->shareRequest->getShareRequestById($id);
	}

	/**
	 * Get an share request by $item_guid and $target_vault_guid
	 *
	 * @param string $item_guid
	 * @param string $target_vault_guid
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getRequestByGuid(string $item_guid, string $target_vault_guid) {
		return $this->shareRequest->getRequestByItemAndVaultGuid($item_guid, $target_vault_guid);
	}

	/**
	 * Get the access control list by item guid
	 *
	 * @param string $item_guid
	 * @return Entity[]
	 */
	public function getCredentialAclList(string $item_guid) {
		return $this->sharingACL->getCredentialAclList($item_guid);
	}

	/**
	 * @param string $item_guid
	 * @return Entity[]
	 * @throws Exception
	 */
	public function getCredentialPendingAclList(string $item_guid) {
		return $this->shareRequest->getRequestsByItemGuidGroupedByUser($item_guid);
	}

	/**
	 * Gets the ACL on the credential for the user
	 *
	 * @param string $user_id
	 * @param string $item_guid
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialAclForUser(string $user_id, string $item_guid) {
		return $this->sharingACL->getItemACL($user_id, $item_guid);
	}

	/**
	 * Get pending share requests by guid
	 *
	 * @param string $item_guid
	 * @return Entity[]
	 */
	public function getShareRequestsByGuid(string $item_guid) {
		return $this->shareRequest->getShareRequestsByItemGuid($item_guid);
	}

	/**
	 * Get pending share requests by guid
	 *
	 * @param ShareRequest $request
	 * @return ShareRequest
	 */
	public function deleteShareRequest(ShareRequest $request) {
		return $this->shareRequest->deleteShareRequest($request);
	}

	/**
	 * Delete ACL
	 *
	 * @param SharingACL|Entity $ACL
	 * @return SharingACL|Entity
	 */
	public function deleteShareACL(SharingACL $ACL) {
		return $this->sharingACL->deleteShareACL($ACL);
	}

	/**
	 * Updates the given ACL entry
	 *
	 * @param SharingACL $sharingACL
	 * @return SharingACL|Entity
	 */
	public function updateCredentialACL(SharingACL $sharingACL) {
		return $this->sharingACL->updateCredentialACL($sharingACL);
	}

	/**
	 * @param ShareRequest $shareRequest
	 * @return ShareRequest
	 */
	public function updateCredentialShareRequest(ShareRequest $shareRequest) {
		return $this->shareRequest->updateShareRequest($shareRequest);
	}


	/**
	 * Get pending share requests by guid and uid
	 *
	 * @param string $item_guid
	 * @param string $user_id
	 * @return Entity[]
	 */
	public function getPendingShareRequestsForCredential(string $item_guid, string $user_id) {
		return $this->shareRequest->getPendingShareRequests($item_guid, $user_id);
	}

	/**
	 * @param string $item_guid
	 * @param string $user_id
	 * @param int $permissions
	 * @return int|IResult
	 * @throws Exception
	 */
	public function updatePendingShareRequestsForCredential(string $item_guid, string $user_id, int $permissions) {
		return $this->shareRequest->updatePendingRequestPermissions($item_guid, $user_id, $permissions);
	}

	/**
	 * Clean up on credential destroyed.
	 * This will delete all ACL's and share requests.
	 * @param string $item_guid
	 */
	public function unshareCredential(string $item_guid) {
		$acl_list = $this->getCredentialAclList($item_guid);
		$request_list = $this->getShareRequestsByGuid($item_guid);
		foreach ($acl_list as $ACL) {
			$this->deleteShareACL($ACL);
		}
		foreach ($request_list as $request) {
			$this->deleteShareRequest($request);
			$notification = $this->IManager->createNotification();
			$notification->setApp('passman')
				->setObject('passman_share_request', $request->getId())
				->setUser($request->getTargetUserId());
			$this->IManager->markProcessed($notification);
		}
	}
}
