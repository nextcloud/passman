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
 * @method void setId(integer $value)
 * @method integer getId()
 * @method void setGuid(string $value)
 * @method string getGuid()
 * @method void setCredentialId(integer $value)
 * @method string getCredentialId()
 * @method void setUserId(string $value)
 * @method string getUserid()
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
			'credential_data' => unserialize(base64_decode($this->getCredentialData())),
            'edited_by' => $this->getEditedBy(),
		];
	}
}