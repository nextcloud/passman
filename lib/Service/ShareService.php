<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 1/10/16
 * Time: 21:54
 */

namespace OCA\Passman\Service;


use OCA\Passman\Db\ShareRequest;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACLMapper;

class ShareService {
    private $sharingACL;
    private $shareRequest;

    public function __construct(SharingACLMapper $sharingACL, ShareRequestMapper $shareRequest) {
        $this->sharingACL = $sharingACL;
        $this->shareRequest = $shareRequest;
    }

    /**
     * Creates requests for all the items on the request array of objects.
     * This array objects must follow this spec:
     *      {
     *          vault_id:   The id of the target vault
     *          guid:       The guid of the target vault
     *          key:        The shared key cyphered with the target vault RSA public key
     *      }
     * @param $target_item_id   string      The shared item ID
     * @param $target_item_guid string      The shared item GUID
     * @param $request_array    array
     * @param $permissions      integer     Must be created with a bitmask from options on the ShareRequest class
	 * @return array						Array of sharing requests
     */
    public function createBulkRequests($target_item_id, $target_item_guid, $request_array, $permissions) {
        $created = (new \DateTime())->getTimestamp();
		$requests = array();
        foreach ($request_array as $req){
            $t = new ShareRequest();
            $t->setItemId($target_item_id);
            $t->setItemGuid($target_item_guid);
            $t->setTargetVaultId($req['vault_id']);
            $t->setTargetVaultGuid($req['guid']);
            $t->setSharedKey($req['key']);
            $t->setPermissions($permissions);
            $t->setCreated($created);
			array_push($requests, $this->shareRequest->createRequest($t));
        }
        return $requests;
    }
}