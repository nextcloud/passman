<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 1/10/16
 * Time: 23:15
 */

namespace OCA\Passman\Db;


use OCP\AppFramework\Db\Entity;

/**
 * @method void setId(integer $value)
 * @method integer getId()
 * @method void setItemId(integer $value)
 * @method integer getItemId()
 * @method void setItemGuid(string $value)
 * @method string getItemGuid()
 * @method void setTargetVaultId(integer $value)
 * @method integer getTargetVaultId()
 * @method void setTargetVaultGuid(integer $value)
 * @method string getTargetVaultGuid()
 * @method void setSharedKey(string $value)
 * @method string getSharedKey()
 * @method void setPermissions(integer $value)
 * @method integer getPermissions()
 * @method void setCreated(integer $value)
 * @method integer getCreated()
 */

class ShareRequest extends Entity implements \JsonSerializable {
    CONST READ  = 0b00000001;
    CONST WRITE = 0b00000010;
    CONST OWNER = 0b10000000;

    protected
        $itemId,
        $itemGuid,
        $targetVaultId,
        $targetVaultGuid,
        $sharedKey,
        $permissions,
        $created;

    public function __construct() {
        // add types in constructor
        $this->addType('itemId', 'integer');
        $this->addType('vaultId', 'integer');
        $this->addType('permissions', 'integer');
    }

    /**
     * Checks wether a user matches one or more permissions at once
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission) {
        $tmp = $this->getPermissions();
        $tmp = $tmp & $permission;
        return $tmp == $permission;
    }

    /**
     * Adds the given permission or permissions set to the user current permissions
     * @param $permission
     */
    public function addPermission($permission) {
        $tmp = $this->getPermissions();
        $tmp = $tmp | $permission;
        $this->setPermissions($tmp);
    }

    /**
     * Takes the given permission or permissions out from the user
     * @param $permission
     */
    public function removePermission($permission) {
        $tmp = $this->getPermissions();
        $tmp = $tmp & !$permission;
        $this->setPermissions($tmp);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'req_id' => $this->getId(),
            'item_id' => $this->getItemId(),
            'item_guid' => $this->getItemGuid(),
            'shared_key' => $this->getSharedKey(),
            'permissions' => $this->getPermissions(),
            'created' => $this->getCreated(),
        ];
    }
}