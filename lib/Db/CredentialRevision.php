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
 * @method void setId(integer $value)
 * @method integer getId()
 * @method void setGuid(string $value)
 * @method string getGuid()
 * @method void setCredentialId(integer $value)
 * @method string getCredentialId()
 * @method void setUserId(string $value)
 * @method string getUserId()
 * @method void setCreated(integer $value)
 * @method integer getCreated()
 * @method void setCredentialData(string $value)
 * @method string getCredentialData()
 * @method void setEditedBy(string $value)
 * @method string getEditedBy()
 */
class CredentialRevision extends Entity implements \JsonSerializable {

	use EntityJSONSerializer;

	protected $guid;
	protected $credentialId;
	protected $userId;
	protected $created;
	protected $credentialData;
    protected $editedBy;


	public function __construct() {
		// add types in constructor
		$this->addType('created', 'integer');
		$this->addType('credentialId', 'integer');
	}

	/**
	 * Turns entity attributes into an array
	 */
	public function jsonSerialize() {
		return [
			'revision_id' => $this->getId(),
			'guid' => $this->getGuid(),
			'created' => $this->getCreated(),
			'credential_data' => json_decode(base64_decode($this->getCredentialData())),
            'edited_by' => $this->getEditedBy(),
		];
	}
}