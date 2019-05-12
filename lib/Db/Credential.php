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
 * @method void setVaultId(integer $value)
 * @method integer getVaultId()
 * @method void setUserId(string $value)
 * @method string getUserId()
 * @method void setLabel(string $value)
 * @method string getLabel()
 * @method void setDescription(string $value)
 * @method string getDescription()
 * @method void setCreated(string $value)
 * @method string getCreated()
 * @method void setChanged(string $value)
 * @method string getChanged()
 * @method void setTags(string $value)
 * @method string getTags()
 * @method void setEmail(string $value)
 * @method string getEmail()
 * @method void setUsername(string $value)
 * @method string getUsername()
 * @method void setPassword(string $value)
 * @method string getPassword()
 * @method void setUrl(string $value)
 * @method string getUrl()
 * @method void setIcon(string $value)
 * @method string getIcon()
 * @method void setRenewInterval(integer $value)
 * @method integer getRenewInterval()
 * @method void setExpireTime(integer $value)
 * @method integer getExpireTime()
 * @method void setDeleteTime(integer $value)
 * @method integer getDeleteTime()
 * @method void setFiles(string $value)
 * @method string getFiles()
 * @method void setCustomFields(string $value)
 * @method string getCustomFields()
 * @method void setOtp(string $value)
 * @method string getOtp()
 * @method void setHidden(bool $value)
 * @method string getHidden()
 * @method void setSharedKey(string $value)
 * @method string getSharedKey()
 * @method void setCompromised(bool $value)
 * @method bool getCompromised()



 */


class Credential extends Entity implements  \JsonSerializable{

	use EntityJSONSerializer;

	protected $guid;
	protected $vaultId;
	protected $userId;
	protected $label;
	protected $description;
	protected $created;
	protected $changed;
	protected $tags;
	protected $email;
	protected $username;
	protected $password;
	protected $url;
	protected $icon;
	protected $renewInterval;
	protected $expireTime;
	protected $deleteTime;
	protected $files;
	protected $customFields;
	protected $otp;
	protected $hidden;
	protected $sharedKey;
	protected $compromised;

	public function __construct() {
		// add types in constructor
		$this->addType('created', 'integer');
		$this->addType('changed', 'integer');
		$this->addType('renewInterval', 'integer');
		$this->addType('expireTime', 'integer');
		$this->addType('deleteTime', 'integer');
		$this->addType('vaultId', 'integer');
		$this->addType('credentialId', 'integer');
		$this->addType('hidden', 'integer');
	}


	/**
	 * Turns entity attributes into an array
	 */
	public function jsonSerialize() {
		return [
			'credential_id' => $this->getId(),
			'guid' => $this->getGuid(),
			'user_id' => $this->getUserId(),
			'vault_id' => $this->getVaultId(),
			'label' => $this->getLabel(),
			'description' => $this->getDescription(),
			'created' => $this->getCreated(),
			'changed' => $this->getChanged(),
			'tags' => $this->getTags(),
			'email' => $this->getEmail(),
			'username' => $this->getUsername(),
			'password' => $this->getPassword(),
			'url' => $this->getUrl(),
			'icon' => json_decode($this->getIcon()),
			'renew_interval' => $this->getRenewInterval(),
			'expire_time' => $this->getExpireTime(),
			'delete_time' => $this->getDeleteTime(),
			'files' => $this->getFiles(),
			'custom_fields' => $this->getCustomFields(),
			'otp' => $this->getOtp(),
			'hidden' => $this->getHidden(),
			'shared_key' => $this->getSharedKey(),
			'compromised' => $this->getCompromised()
		];
	}
}