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


use Icewind\SMB\Share;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;

class ShareRequestMapper extends Mapper {
    const TABLE_NAME = 'passman_share_request';

    public function __construct(IDBConnection $db) {
        parent::__construct($db, self::TABLE_NAME);
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
    public function getRequestByItemAndVaultGuid($item_guid, $target_vault_guid){
        $q = "SELECT * FROM *PREFIX*" . self::TABLE_NAME . " WHERE item_guid = ? AND target_vault_guid = ?";
        return $this->findEntity($q, [$item_guid, $target_vault_guid]);
    }

    /**
     * Get shared items for the given item_guid
     * @param $item_guid
     * @return ShareRequest[]
     */
    public function getRequestsByItemGuidGroupedByUser($item_guid){
    	if (strtolower($this->db->getDatabasePlatform()->getName()) === 'mysql'){
    		$this->db->executeQuery("SET sql_mode = '';");
		}
        $q = "SELECT *, target_user_id FROM *PREFIX*" . self::TABLE_NAME . " WHERE item_guid = ? GROUP BY target_user_id;";
        return $this->findEntities($q, [$item_guid]);
    }

    /**
     * Deletes all pending requests for the given user to the given item
     * @param $item_id          The item ID
     * @param $target_user_id   The target user
     * @return \PDOStatement    The result of running the db query
     */
    public function cleanItemRequestsForUser($item_id, $target_user_id){
		$q = "DELETE FROM *PREFIX*" . self::TABLE_NAME . " WHERE item_id = ? AND target_user_id = ?";
		$this->execute($q, [$item_id, $target_user_id]);
        return $this->execute($q, [$item_id, $target_user_id]);
    }

    /**
     * Obtains all pending share requests for the given user ID
     * @param $user_id
     * @return ShareRequest[]
     */
    public function getUserPendingRequests($user_id){
        $q = "SELECT * FROM *PREFIX*". self::TABLE_NAME ." WHERE target_user_id = ?";
        return $this->findEntities($q, [$user_id]);
    }

    /**
     * Deletes the given share request
     * @param ShareRequest $shareRequest    Request to delete
     * @return ShareRequest                 The deleted request
     */
    public function deleteShareRequest(ShareRequest $shareRequest){
    	return $this->delete($shareRequest);
	}

    /**
     * Gets a share request by it's unique incremental id
     * @param $id
     * @return ShareRequest
	 * @throws DoesNotExistException
     */
	public function getShareRequestById($id){
		$q = "SELECT * FROM *PREFIX*" . self::TABLE_NAME . " WHERE id = ?";
		return $this->findEntity($q, [$id]);
	}

    /**
     * Gets all share requests by a given item GUID
     * @param $item_guid
     * @return ShareRequest[]
     */
	public function getShareRequestsByItemGuid($item_guid){
		$q = "SELECT * FROM *PREFIX*" . self::TABLE_NAME . " WHERE 	item_guid = ?";
		return $this->findEntities($q, [$item_guid]);
	}

    /**
     * Updates the given share request,
     * @param ShareRequest $shareRequest
     * @return ShareRequest
     */
	public function updateShareRequest(ShareRequest $shareRequest){
		return $this->update($shareRequest);
	}

    /**
     * Finds pending requests sent to the given user to the given item.
     * @param $item_guid
     * @param $user_id
     * @return ShareRequest[]
     */
	public function getPendingShareRequests($item_guid, $user_id){
		$q = "SELECT * FROM *PREFIX*" . self::TABLE_NAME . " WHERE 	item_guid = ? and target_user_id= ?";
		return $this->findEntities($q, [$item_guid, $user_id]);
	}

    /**
     * Updates all pending requests with the given permissions
     * @param $item_guid        The item for which to update the requests
     * @param $user_id          The user for which to update the requests
     * @param $permissions      The new permissions to apply
     * @return \PDOStatement    The result of the operation
     */
	public function updatePendingRequestPermissions($item_guid, $user_id, $permissions){
	    $q = "UPDATE *PREFIX*" . self::TABLE_NAME . " SET permissions = ? WHERE item_guid = ? AND target_user_id = ?";
        return $this->execute($q, [$permissions, $item_guid, $user_id]);
    }

}