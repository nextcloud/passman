<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 24/09/16
 * Time: 14:20
 */

namespace OCA\Passman\Db;


use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use OCP\IUser;
use OCA\Passman\Utility\Utils;

class SharingACLMapper extends Mapper {
    const TABLE_NAME = '`*PREFIX*passman_sharing_acl`';

    public function __construct(IDBConnection $db, Utils $utils) {
        parent::__construct($db, 'passman_vaults');
        $this->utils = $utils;
    }

    /**
     * Gets all the credential data for the given user
     * @param $userId
     * @param $item_guid
     * @return SharingACL[]
     */
    public function getCredentialPermissions(IUser $userId, $item_guid){
        $sql = "SELECT * FROM {{self::TABLE_NAME}} WHERE user_id = ? AND item_guid = ?";

        return $this->findEntities($sql, [$userId, $item_guid]);
    }

    public function createACLEntry(SharingACL $acl){
        return $this->insert($acl);
    }
}