<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 1/10/16
 * Time: 23:15
 */

namespace OCA\Passman\Db;


use OCA\Passman\Utility\PermissionEntity;
use OCP\AppFramework\Db\Entity;

/**
 * @method void setId(integer $value)
 * @method integer getId()
 * @method void setItemId(integer $value)
 * @method integer getItemId()
 * @method void setItemGuid(string $value)
 * @method string getItemGuid()
 * @method void setTargetUserId(string $value)
 * @method string getTargetUserId()
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
 * @method void setFromUserId(integer $value)
 * @method integer getFromUserId()
 */

class ShareRequest extends PermissionEntity implements \JsonSerializable {

    protected
        $itemId,
        $itemGuid,
        $targetUserId,
        $targetVaultId,
        $targetVaultGuid,
        $sharedKey,
        $permissions,
        $created,
		$fromUserId;

    public function __construct() {
        // add types in constructor
        $this->addType('itemId', 'integer');
        $this->addType('vaultId', 'integer');
        $this->addType('permissions', 'integer');
		$this->addType('created', 'integer');
		$this->addType('targetVaultId', 'integer');
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
            'target_user_id' => $this->getTargetUserId(),
            'target_vault_id' => $this->getTargetVaultId(),
            'target_vault_guid' => $this->getTargetVaultGuid(),
            'from_user_id' => $this->getFromUserId(),
            'shared_key' => $this->getSharedKey(),
            'permissions' => $this->getPermissions(),
            'created' => $this->getCreated(),
        ];
    }

    function asACLJson(){
        return [
            'item_id' => $this->getItemId(),
            'item_guid' => $this->getItemGuid(),
            'user_id' => $this->getTargetUserId(),
            'created' => $this->getCreated(),
            'permissions' => $this->getPermissions(),
            'vault_id' => $this->getTargetVaultId(),
            'vault_guid' => $this->getTargetVaultGuid(),
            'shared_key' => $this->getSharedKey(),
            'pending'   => true,
        ];
    }
}