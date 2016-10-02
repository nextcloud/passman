<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 1/10/16
 * Time: 21:54
 */

namespace OCA\Passman\Service;


use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACL;
use OCA\Passman\Db\SharingACLMapper;

class ShareService {
    private $sharingACL;
    private $shareRequest;
    private $credential;

    public function __construct(SharingACLMapper $sharingACL, ShareRequestMapper $shareRequest, CredentialMapper $credentials) {
        $this->sharingACL = $sharingACL;
        $this->shareRequest = $shareRequest;
        $this->credential = $credentials;
    }

    /**
     * Creates requests for all the items on the request array of objects.
     * This array must follow this spec:
     *      user_id:    The target user id
     *      vault_id:   The id of the target vault
     *      guid:       The guid of the target vault
     *      key:        The shared key cyphered with the target vault RSA public key
     * @param $target_item_id   string      The shared item ID
     * @param $target_item_guid string      The shared item GUID
     * @param $request_array    array
     * @param $permissions      integer     Must be created with a bitmask from options on the ShareRequest class
	 * @return array						Array of sharing requests
     */
    public function createBulkRequests($target_item_id, $target_item_guid, $request_array, $permissions, $credential_owner) {
        $created = (new \DateTime())->getTimestamp();
		$requests = array();
        foreach ($request_array as $req){
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
     * Applies the given share, defaults to no expire
     * @param $item_guid
     * @param $target_vault_guid
     * @param $final_shared_key
     */
    public function applyShare($item_guid, $target_vault_guid, $final_shared_key){
        $request = $this->shareRequest->getRequestByGuid($item_guid, $target_vault_guid);
        $permissions = $request->getPermissions();

        $acl = new SharingACL();
        $acl->setItemId($request->getItemId());
        $acl->setItemGuid($request->getItemGuid());
        $acl->setUserId($request->getTargetUserId());
        $acl->setCreated($request->getCreated());
        $acl->setExpire(0);
        $acl->setPermissions($permissions);
        $acl->setVaultId($request->getTargetVaultId());
        $acl->getVaultGuid($request->getTargetVaultGuid());
        $acl->setSharedKey($final_shared_key);

        $this->sharingACL->createACLEntry($acl);
        $this->shareRequest->cleanItemRequestsForUser($request->getItemId(), $request->getTargetUserId());
    }

    /**
     * Obtains pending requests for the given user ID
     * @param $user_id
     * @return \OCA\Passman\Db\ShareRequest[]
     */
    public function getUserPendingRequests($user_id){
        return $this->shareRequest->getUserPendingRequests($user_id);
    }

    public function getSharedItems($user_id, $vault_id){
        $entries = $this->sharingACL->getVaultEntries($user_id, $vault_id);
        $return = [];
        foreach ($entries as $entry){
            $tmp = $entry->jsonSerialize();
            $tmp['credential_data'] = $this->credential->getCredentialById($entry->getItemId());
            $return[] = $tmp;
        }
        return $return;
    }


	/**
	 * Deletes an share reuqest by id
	 * @param $share_request_id
	 *
	 */
	public function deleteShareRequestById($id){
		$t = new ShareRequest();
		$t->setId($id);
		$this->shareRequest->deleteShareRequest($t);

	}
}