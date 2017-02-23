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
 * @method void setVaultGuid(string $value)
 * @method string getVaultGuid()
 * @method void setReason(string $value)
 * @method string getReason()
 * @method void setRequestedBy(string $value)
 * @method string getRequestedBy()
 * @method void setCreated(integer $value)
 * @method integer getCreated()
 */


class DeleteVaultRequest extends Entity implements  \JsonSerializable{

	use EntityJSONSerializer;

	protected $vaultGuid;
	protected $reason;
	protected $requestedBy;
	protected $created;

	public function __construct() {
		// add types in constructor
		$this->addType('id', 'integer');
		$this->addType('created', 'integer');
	}
	/**
	 * Turns entity attributes into an array
	 */
	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'vault_guid' => $this->getVaultGuid(),
			'reason' => $this->getReason(),
			'requested_by' => $this->getRequestedBy(),
			'created' => $this->getCreated(),
		];
	}
}