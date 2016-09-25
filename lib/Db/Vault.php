<?php
/**
 * Nextcloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Sander Brand <brantje@gmail.com>
 * @copyright Sander Brand 2016
 */

namespace OCA\Passman\Db;
use \OCP\AppFramework\Db\Entity;

/**
 * @method integer getId()
 * @method void setId(integer $value)
 * @method void setName(string $value)
 * @method string getName()
 * @method void setGuid(string $value)
 * @method string getGuid()
 * @method void setUserId(string $value)
 * @method string getUserid()
 * @method void setCreated(integer $value)
 * @method integer getCreated()
 * @method void setLastAccess(integer $value)
 * @method integer getLastAccess()
 * @method void setPublicSharingKey(string $value)
 * @method string getPublicSharingKey()
 * @method void setPrivateSharingKey(string $value)
 * @method string getPrivateSharingKey()
 * @method void setSharingKeysGenerated(integer $value)
 * @method integer getSharingKeysGenerated()
 */


class Vault extends Entity implements  \JsonSerializable{

	use EntityJSONSerializer;

	protected $guid;
	protected $name;
	protected $userId;
	protected $created;
	protected $lastAccess;
	protected $publicSharingKey;
	protected $privateSharingKey;
	protected $sharingKeysGenerated;
	public function __construct() {
		// add types in constructor
		$this->addType('created', 'integer');
		$this->addType('lastAccess', 'integer');
	}
	/**
	 * Turns entity attributes into an array
	 */
	public function jsonSerialize() {
		return [
			'vault_id' => $this->getId(),
			'guid' => $this->getGuid(),
			'name' => $this->getName(),
			'created' => $this->getCreated(),
			'private_sharing_key' => $this->getPrivateSharingKey(),
			'public_sharing_key' => $this->getPublicSharingKey(),
			'sharing_keys_generated' => $this->getSharingKeysGenerated(),
			'last_access' => $this->getlastAccess(),
		];
	}
}