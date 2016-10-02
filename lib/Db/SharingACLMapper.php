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
        parent::__construct($db, 'passman_sharing_acl');
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

    /**
     * Gets the currently accepted share requests from the given user for the given vault guid
     * @param $user_id
     * @param $vault_id
     * @return SharingACL[]
     */
    public function getVaultEntries($user_id, $vault_id) {
        $q = "SELECT * FROM {{ self::TABLE_NAME }} WHERE target_user_id = ? AND target_vault_guid = ?";
        return $this->findEntities($q, [$user_id, $vault_id]);
    }
}