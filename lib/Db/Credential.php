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
 * @method void setFavicon(string $value)
 * @method string getFavicon()
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
	protected $favicon;
	protected $renewInterval;
	protected $expireTime;
	protected $deleteTime;
	protected $files;
	protected $customFields;
	protected $otp;
	protected $hidden;

	public function __construct() {
		// add types in constructor
		$this->addType('created', 'integer');
		$this->addType('changed', 'integer');
		$this->addType('renewInterval', 'integer');
		$this->addType('expireTime', 'integer');
		$this->addType('deleteTime', 'integer');
	}


	/**
	 * Turns entitie attributes into an array
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
			'favicon' => $this->getFavicon(),
			'renew_interval' => $this->getRenewInterval(),
			'expire_time' => $this->getExpireTime(),
			'delete_time' => $this->getDeleteTime(),
			'files' => $this->getFiles(),
			'custom_fields' => $this->getCustomFields(),
			'otp' => $this->getOtp(),
			'hidden' => $this->getHidden(),
		];
	}
}