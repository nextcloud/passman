<?php
/**
 * User: Marcos Zuriaga
 * Date: 24/09/16
 * Time: 14:19
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
 * @method void setUserId(string $value)
 * @method string getUserid()
 * @method void setGroupId(string $value)
 * @method string getGroupId()
 * @method void setCreated(integer $value)
 * @method integer getCreated()
 * @method void setExpire(integer $value)
 * @method integer getExpire()
 * @method void setPermissions(integer $value)
 * @method integer getPermissions()
 */

class SharingACL extends Entity implements \JsonSerializable
{

    public function __construct() {
        // add types in constructor
        $this->addType('itemId', 'integer');
        $this->addType('created', 'integer');
        $this->addType('expire', 'integer');
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
            'user_id' => $this->getUserid(),
            'group_id' => $this->getGroupId(),
            'created' => $this->getCreated(),
            'expire' => $this->getExpire(),
            'permissions' => $this->getPermissions(),
        ];
    }
}