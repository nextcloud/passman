<?php
/**
 * User: Marcos Zuriaga
 * Date: 24/09/16
 * Time: 14:19
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
 * @method void setUserId(string $value)
 * @method string getUserId()
 * @method void setCreated(integer $value)
 * @method integer getCreated()
 * @method void setExpire(integer $value)
 * @method integer getExpire()
 * @method void setExpireViews(integer $value)
 * @method integer getExpireViews()
 * @method void setPermissions(integer $value)
 * @method integer getPermissions()
 * @method void setVaultId(integer $value)
 * @method integer getVaultId()
 * @method void setVaultGuid(string $vault)
 * @method string getVaultGuid()
 * @method void setSharedKey(string $value)
 * @method string getSharedKey()
 */

class SharingACL extends PermissionEntity implements \JsonSerializable
{

    protected
        $itemId,
        $itemGuid,
        $userId,
        $created,
        $expire,
		$expireViews,
        $permissions,
        $vaultId,
        $vaultGuid,
        $sharedKey;


    public function __construct() {
        // add types in constructor
        $this->addType('itemId', 'integer');
        $this->addType('created', 'integer');
        $this->addType('expire', 'integer');
        $this->addType('expireViews', 'integer');
        $this->addType('permissions', 'integer');
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
            'acl_id' => $this->getId(),
            'item_id' => $this->getItemId(),
            'item_guid' => $this->getItemGuid(),
            'user_id' => $this->getUserId(),
            'created' => $this->getCreated(),
            'expire' => $this->getExpire(),
            'expire_views' => $this->getExpireViews(),
            'permissions' => $this->getPermissions(),
            'vault_id' => $this->getVaultId(),
            'vault_guid' => $this->getVaultGuid(),
            'shared_key' => $this->getSharedKey(),
            'pending' => false,
        ];
    }
}