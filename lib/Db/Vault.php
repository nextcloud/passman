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
use \OCP\AppFramework\Db\Entity;

/**
 * @method integer getId()
 * @method void setId(integer $value)
 * @method void setName(string $value)
 * @method string getName()
 * @method void setGuid(string $value)
 * @method string getGuid()
 * @method void setUserId(string $value)
 * @method string getUserId()
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
 * @method void setVaultSettings(string $value)
 * @method string getVaultSettings()
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
	protected $vaultSettings;
	
	public function __construct() {
		// add types in constructor
		$this->addType('created', 'integer');
		$this->addType('lastAccess', 'integer');
		$this->addType('sharingKeysGenerated', 'integer');
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
			'public_sharing_key' => $this->getPublicSharingKey(),
			'last_access' => $this->getlastAccess(),
		];
	}
}