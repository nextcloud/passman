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

namespace OCA\Passman\Db;


use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use OCP\IUser;
use OCA\Passman\Utility\Utils;

class SharingACLMapper extends Mapper {
    const TABLE_NAME = '*PREFIX*passman_sharing_acl';

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'passman_sharing_acl');
    }

    public function createACLEntry(SharingACL $acl){
        return $this->insert($acl);
    }

    /**
     * Gets the currently accepted share requests from the given user for the given vault guid
     * @param $user_id
     * @param $vault_guid
     * @return SharingACL[]
     */
    public function getVaultEntries($user_id, $vault_guid) {
        $q = "SELECT * FROM ". self::TABLE_NAME ." WHERE user_id = ? AND vault_guid = ?";
        return $this->findEntities($q, [$user_id, $vault_guid]);
    }

    /**
     * Gets the acl for a given item guid
     * @param $user_id
     * @param $item_guid
     * @return SharingACL
     */
    public function getItemACL($user_id, $item_guid) {
        $q = "SELECT * FROM " . self::TABLE_NAME . " WHERE item_guid = ? AND ";
        $filter = [$item_guid];
        if ($user_id === null){
            $q .= 'user_id is null';
        }
        else {
            $q .= 'user_id = ? ';
            $filter[] = $user_id;
        }
        return $this->findEntity($q, $filter);
    }

    /**
     * Update the acl for a given item guid
     * @param $user_id
     * @param $item_guid
     * @return SharingACL
     */
    public function updateCredentialACL(SharingACL $sharingACL) {
        return $this->update($sharingACL);
    }

    /**
     * Gets the currently accepted share requests from the given user for the given vault guid
     * @param $user_id
     * @param $vault_id
     * @return SharingACL[]
     */
    public function getCredentialAclList($item_guid) {
        $q = "SELECT * FROM ". self::TABLE_NAME ." WHERE item_guid = ?";
        return $this->findEntities($q, [$item_guid]);
    }

    public function deleteShareACL(SharingACL $ACL){
    	return $this->delete($ACL);
	}
}