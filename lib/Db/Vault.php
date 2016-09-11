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
 * @method void setlastAccess(integer $value)
 * @method integer getlastAccess()
 */


class Vault extends Entity implements  \JsonSerializable{

	use EntityJSONSerializer;

	protected $guid;
	protected $name;
	protected $userId;
	protected $created;
	protected $lastAccess;

	public function __construct() {
		// add types in constructor
		$this->addType('created', 'integer');
		$this->addType('lastAccess', 'integer');
	}

	public static function fromRow($row){
		$vault = new Vault();
		$vault->setId($row['id']);
		$vault->setGuid($row['guid']);
		$vault->setName($row['name']);
		$vault->setCreated($row['created']);
		$vault->setlastAccess($row['last_access']);
		return $vault;
	}

	/**
	 * Turns entitie attributes into an array
	 */
	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'guid' => $this->getGuid(),
			'name' => $this->getName(),
			'created' => $this->getCreated(),
			'last_access' => $this->getlastAccess(),
		];
	}
}