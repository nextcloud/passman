<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 1/10/16
 * Time: 23:15
 */

namespace OCA\Passman\Db;


use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;

class ShareRequestMapper extends Mapper {
    const TABLE_NAME = 'passman_share_request';

    public function __construct(IDBConnection $db, Utils $utils) {
        parent::__construct($db, self::TABLE_NAME);
        $this->utils = $utils;
    }

    public function createRequest(ShareRequest $request){
        return $this->insert($request);
    }

    /**
     * Obtains a request by the given item and vault GUID pair
     * @param $item_guid
     * @param $target_vault_guid
     * @return ShareRequest
     */
    public function getRequestByGuid($item_guid, $target_vault_guid){
        $q = "SELECT * FROM *PREFIX*" . self::TABLE_NAME . " WHERE item_guid = ? AND target_vault_guid = ?";
        return $this->findEntity($q, [$item_guid, $target_vault_guid]);
    }

    public function cleanItemRequestsForUser($item_id, $target_user_id){
        $req = new ShareRequest();
        $req->setItemId($item_id);
        $req->setTargetUserId($target_user_id);
        return $this->delete($req);
    }

    /**
     * Obtains all pending share requests for the given user ID
     * @param $user_id
     * @return ShareRequest[]
     */
    public function getUserPendingRequests($user_id){
        $q = "SELECT * FROM *PREFIX*{{self::TABLE_NAME }} WHERE user_id = ?";
        return $this->findEntities($q, [$user_id]);
    }
}