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
use OCA\Passman\Db\CredentialRevision;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\Exception;
use OCP\Notification\IManager;

class ShareService {
	public function __construct(
		private readonly SharingACLMapper $sharingACL,
		private readonly ShareRequestMapper $shareRequest,
		private readonly CredentialMapper $credential,
		private readonly CredentialRevisionService $revisions,
		private readonly EncryptService $encryptService,
		private readonly IManager $IManager,
	) {
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
		$requests = [];
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
			$requests[] = $this->shareRequest->createRequest($t);
		}
		return $requests;
	}

	/**
	 * @param SharingACL $acl
	 * @return SharingACL
	 */
	public function createACLEntry(SharingACL $acl): SharingACL {
		if ($acl->getCreated() === null) {
			$acl->setCreated((new \DateTime())->getTimestamp());
		}
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
	public function applyShare(string $item_guid, string $target_vault_guid, string $final_shared_key): void {
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
	 * @return SharingACL[]
	 */
	public function getUserPendingRequests(string $user_id): array {
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
	public function getSharedItems(string $user_id, string $vault_guid): array {
		$acceptedVaultShareRequests = $this->sharingACL->getVaultEntries($user_id, $vault_guid);

		$return = [];
		foreach ($acceptedVaultShareRequests as $acceptedVaultShareRequest) {
			$serializableVaultShareRequest = $this->prepareSharedItemForResponse($acceptedVaultShareRequest);
			if (!empty($serializableVaultShareRequest)) {
				$return[] = $serializableVaultShareRequest;
			}
		}
		return $return;
	}

	/**
	 * Get shared credential (from a user)
	 *
	 * @param string|null $user_id
	 * @param string $item_guid
	 * @return array
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getSharedItem(?string $user_id, string $item_guid): array {
		$acl = $this->sharingACL->getItemACL($user_id, $item_guid);

		$serializableItemACL = $this->prepareSharedItemForResponse($acl);
		if (empty($serializableItemACL)) {
			throw new DoesNotExistException("Item not found or wrong access level");
		}
		return $serializableItemACL;
	}

	/**
	 * @throws MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	private function prepareSharedItemForResponse(SharingACL $sharingACL): array|null {
		// Check if the user can read the credential, probably unnecessary, but just to be sure
		if (!$sharingACL->hasPermission(SharingACL::READ)) {
			return null;
		}

		$credential = $this->credential->getCredentialById($sharingACL->getItemId());
		$credential = $this->encryptService->decryptCredential($credential);

		$serializableSharingACL = $sharingACL->jsonSerialize();
		$serializableSharingACL['credential_data'] = $credential->jsonSerialize();

		if (!$sharingACL->hasPermission(SharingACL::FILES)) {
			unset($serializableSharingACL['credential_data']['files']);
		}
		unset($serializableSharingACL['credential_data']['shared_key']);
		return $serializableSharingACL;
	}

	/**
	 * Gets the acl for a given item guid
	 *
	 * @param string|null $user_id
	 * @param string $item_guid
	 * @return SharingACL
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getACL(?string $user_id, string $item_guid): SharingACL {
		return $this->sharingACL->getItemACL($user_id, $item_guid);
	}

	/**
	 * Gets history from the given item checking the user's permissions to access it
	 *
	 * @param string|null $user_id
	 * @param string $item_guid
	 * @return array|CredentialRevision[]
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getItemHistory(?string $user_id, string $item_guid): array {
		$acl = $this->sharingACL->getItemACL($user_id, $item_guid);
		if (!$acl->hasPermission(SharingACL::READ | SharingACL::HISTORY)) {
			return [];
		}

		return $this->revisions->getRevisions($acl->getItemId());
	}


	/**
	 * Deletes a share request by the item id
	 *
	 * @param ShareRequest $request
	 * @return int
	 * @throws Exception
	 */
	public function cleanItemRequestsForUser(ShareRequest $request): int {
		return $this->shareRequest->cleanItemRequestsForUser($request->getItemId(), $request->getTargetUserId());
	}

	/**
	 * Get a share request by id
	 *
	 * @param int $id
	 * @return ShareRequest
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getShareRequestById(int $id): ShareRequest {
		return $this->shareRequest->getShareRequestById($id);
	}

	/**
	 * Get a share request by $item_guid and $target_vault_guid
	 *
	 * @param string $item_guid
	 * @param string $target_vault_guid
	 * @return ShareRequest
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getRequestByGuid(string $item_guid, string $target_vault_guid): ShareRequest {
		return $this->shareRequest->getRequestByItemAndVaultGuid($item_guid, $target_vault_guid);
	}

	/**
	 * Get the access control list by item guid
	 *
	 * @param string $item_guid
	 * @return SharingACL[]
	 */
	public function getCredentialAclList(string $item_guid): array {
		return $this->sharingACL->getCredentialAclList($item_guid);
	}

	/**
	 * Get the access control list by vault guid
	 *
	 * @param string $user_id
	 * @param string $vault_guid
	 * @return SharingACL[]
	 */
	public function getVaultAclList(string $user_id, string $vault_guid): array {
		return $this->sharingACL->getVaultEntries($user_id, $vault_guid);
	}

	/**
	 * @param string $item_guid
	 * @return ShareRequest[]
	 * @throws Exception
	 */
	public function getCredentialPendingAclList(string $item_guid): array {
		return $this->shareRequest->getRequestsByItemGuid($item_guid);
	}

	/**
	 * Gets the ACL on the credential for the user
	 *
	 * @param string|null $user_id
	 * @param string $item_guid
	 * @return SharingACL
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function getCredentialAclForUser(?string $user_id, string $item_guid): SharingACL {
		return $this->sharingACL->getItemACL($user_id, $item_guid);
	}

	/**
	 * Get pending share requests by guid
	 *
	 * @param string $item_guid
	 * @return ShareRequest[]
	 */
	public function getShareRequestsByGuid(string $item_guid): array {
		return $this->shareRequest->getShareRequestsByItemGuid($item_guid);
	}

	/**
	 * Get pending share requests by guid
	 *
	 * @param ShareRequest $request
	 * @return ShareRequest
	 */
	public function deleteShareRequest(ShareRequest $request): ShareRequest {
		return $this->shareRequest->deleteShareRequest($request);
	}

	/**
	 * Delete ACL
	 *
	 * @param SharingACL $sharingACL
	 * @return SharingACL
	 */
	public function deleteShareACL(SharingACL $sharingACL): SharingACL {
		return $this->sharingACL->deleteShareACL($sharingACL);
	}

	/**
	 * Updates the given ACL entry
	 *
	 * @param SharingACL $sharingACL
	 * @return SharingACL
	 */
	public function updateCredentialACL(SharingACL $sharingACL): SharingACL {
		return $this->sharingACL->updateCredentialACL($sharingACL);
	}

	/**
	 * @param ShareRequest $shareRequest
	 * @return ShareRequest
	 */
	public function updateCredentialShareRequest(ShareRequest $shareRequest): ShareRequest {
		return $this->shareRequest->updateShareRequest($shareRequest);
	}


	/**
	 * Get pending share requests by guid and uid
	 *
	 * @param string $item_guid
	 * @param string $user_id
	 * @return ShareRequest[]
	 */
	public function getPendingShareRequestsForCredential(string $item_guid, string $user_id): array {
		return $this->shareRequest->getPendingShareRequests($item_guid, $user_id);
	}

	/**
	 * @param string $item_guid
	 * @param string $user_id
	 * @param int $permissions
	 * @return int
	 * @throws Exception
	 */
	public function updatePendingShareRequestsForCredential(string $item_guid, string $user_id, int $permissions): int {
		return $this->shareRequest->updatePendingRequestPermissions($item_guid, $user_id, $permissions);
	}

	/**
	 * Clean up on credential destroyed.
	 * This will delete all ACL's and share requests.
	 * @param string $item_guid
	 */
	public function unshareCredential(string $item_guid): void {
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
