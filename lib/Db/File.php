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
 * @method void setGuid(string $value)
 * @method string getGuid()
 * @method void setUserId(string $value)
 * @method string getUserid()
 * @method void setMimetype(string $value)
 * @method string getMimetype()
 * @method void setFilename(string $value)
 * @method string getFilename()
 * @method void setSize(integer $value)
 * @method integer getSize()
 * @method void setCreated(integer $value)
 * @method integer getCreated()
 * @method void setFileData(string $value)
 * @method string getFileData()
 */


class File extends Entity implements  \JsonSerializable {

	use EntityJSONSerializer;

	protected $guid;
	protected $userId;
	protected $mimetype;
	protected $filename;
	protected $size;
	protected $created;
	protected $fileData;

	public function __construct() {
		// add types in constructor
		$this->addType('created', 'integer');
		$this->addType('size', 'integer');
	}
	/**
	 * Turns entity attributes into an array
	 */
	public function jsonSerialize() {
		return [
			'file_id' => $this->getId(),
			'filename' => $this->getFilename(),
			'guid' => $this->getGuid(),
			'size' => $this->getSize(),
			'file_data' => $this->getFileData(),
			'created' => $this->getCreated(),
			'mimetype' => $this->getMimetype(),
		];
	}
}