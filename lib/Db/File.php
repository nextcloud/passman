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
  * @method void setGuid(string $value)
 * @method string getGuid()
 * @method void setUserId(string $value)
 * @method string getUserId()
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


class File extends Entity implements  \JsonSerializable{

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