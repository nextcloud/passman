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

namespace OCA\Passman\Service;


// Class copied from http://stackoverflow.com/questions/5089841/two-way-encryption-i-need-to-store-passwords-that-can-be-retrieved?answertab=votes#tab-top
// Upgraded to use openssl
use OCA\Passman\Db\Credential;
use OCA\Passman\Db\File;
use OCP\AppFramework\Db\Entity;
use OCP\IConfig;

/**
 * A class to handle secure encryption and decryption of arbitrary data
 *
 *  Note that this is not just straight encryption. It also has a few other
 *  features in it to make the encrypted data far more secure.  Note that any
 *  other implementations used to decrypt data will have to do the same exact
 *  operations.
 *
 * Security Benefits:
 *
 * - Uses Key stretching
 * - Hides the Initialization Vector
 * - Does HMAC verification of source data
 *
 */
class EncryptService {

	/**
	 * Supported cipher algorithms accompanied by their key/block sizes in bytes
	 *
	 * OpenSSL has no equivalent of mcrypt_get_key_size() and mcrypt_get_block_size() hence sizes stored here.
	 *
	 * @var array
	 */
	const SUPPORTED_ALGORITHMS = array(
		'aes-256-cbc' => array('name' => 'AES-256', 'keySize' => 32, 'blockSize' => 32),
		'bf' => array('name' => 'BF', 'keySize' => 16, 'blockSize' => 8),
		'des' => array('name' => 'DES', 'keySize' => 7, 'blockSize' => 8),
		'des-ede3' => array('name' => 'DES-EDE3', 'keySize' => 21, 'blockSize' => 8), // 3 different 56-bit keys
		'cast5' => array('name' => 'CAST5', 'keySize' => 16, 'blockSize' => 8),
	);

	const OP_ENCRYPT = 'encrypt';
	const OP_DECRYPT = 'decrypt';

	// The fields of a credential which are encrypted
	public $encrypted_credential_fields = array(
		'description', 'username', 'password', 'files', 'custom_fields', 'otp', 'email', 'tags', 'url', 'icon'
	);

	// Contains the server key
	private $server_key;

	/**
	 * @var string $cipher The openssl cipher to use for this instance
	 */
	protected $cipher = '';

	/**
	 * @var int $rounds The number of rounds to feed into PBKDF2 for key generation
	 */
	protected $rounds = 100;

	/**
	 * EncryptService constructor.
	 * @param SettingsService $settings
	 * @param IConfig $config
	 */
	public function __construct(SettingsService $settings, IConfig $config) {
		$this->cipher = $settings->getAppSetting('server_side_encryption', 'aes-256-cbc');
		$password_salt = $config->getSystemValue('passwordsalt', '');
		$secret = $config->getSystemValue('secret', '');
		$this->server_key = $password_salt . $secret;
		$this->rounds = $settings->getAppSetting('rounds_pbkdf2_stretching', 100);
	}

	/**
	 * Create an encryption key. Based on given parameters
	 *
	 * @param string $userKey The user key to use. This should be specific to this user.
	 * @param string $serverKey The server key
	 * @param string $userSuppliedKey A key from the credential (eg guid, name or tags)
	 * @return string
	 */

	public static function makeKey($userKey, $serverKey, $userSuppliedKey) {
		$key = hash_hmac('sha512', $userKey, $serverKey);
		$key = hash_hmac('sha512', $key, $userSuppliedKey);
		return $key;
	}

	/**
	 * Get the maximum key size for the selected cipher and mode of operation
	 *
	 * @return int Value is in bytes
	 */
	public function getKeySize() {
		return EncryptService::SUPPORTED_ALGORITHMS[$this->cipher]['keySize'];
	}

	/**
	 * Decrypt the data with the provided key
	 *
	 * @param string $data_hex The encrypted datat to decrypt
	 * @param string $key The key to use for decryption
	 *
	 * @returns string|false The returned string if decryption is successful
	 *                           false if it is not
	 */
	public function decrypt($data_hex, $key) {

		if (!function_exists('hex2bin')) {
			function hex2bin($str) {
				$sbin = "";
				$len = strlen($str);
				for ($i = 0; $i < $len; $i += 2) {
					$sbin .= pack("H*", substr($str, $i, 2));
				}

				return $sbin;
			}
		}

		$data = hex2bin($data_hex);

		$salt = substr($data, 0, 128);
		$enc = substr($data, 128, -64);
		$mac = substr($data, -64);

		list ($cipherKey, $macKey, $iv) = $this->getKeys($salt, $key);

		if (!$this->hash_equals(hash_hmac('sha512', $enc, $macKey, true), $mac)) {
			return false;
		}

		$dec = openssl_decrypt($enc, $this->cipher, $cipherKey, true, $iv);
		$data = $this->unpad($dec);

		return $data;
	}

	/**
	 * Encrypt the supplied data using the supplied key
	 *
	 * @param string $data The data to encrypt
	 * @param string $key The key to encrypt with
	 *
	 * @returns string The encrypted data
	 */
	public function encrypt($data, $key) {
		if (function_exists('random_bytes')) {
			$salt = random_bytes(128);
		} else {
			$salt = openssl_random_pseudo_bytes(128);
		}
		list ($cipherKey, $macKey, $iv) = $this->getKeys($salt, $key);
		$data = $this->pad($data);
		$enc = openssl_encrypt($data, $this->cipher, $cipherKey, true, $iv);
		$mac = hash_hmac('sha512', $enc, $macKey, true);
		$data = bin2hex($salt . $enc . $mac);
		return $data;

	}

	/**
	 * Generates a set of keys given a random salt and a master key
	 *
	 * @param string $salt A random string to change the keys each encryption
	 * @param string $key The supplied key to encrypt with
	 *
	 * @returns array An array of keys (a cipher key, a mac key, and a IV)
	 */
	protected function getKeys($salt, $key) {
		$ivSize = openssl_cipher_iv_length($this->cipher);
		$keySize = openssl_cipher_iv_length($this->cipher);
		$length = 2 * $keySize + $ivSize;

		$key = $this->pbkdf2('sha512', $key, $salt, $this->rounds, $length);

		$cipherKey = substr($key, 0, $keySize);
		$macKey = substr($key, $keySize, $keySize);
		$iv = substr($key, 2 * $keySize);
		return array($cipherKey, $macKey, $iv);
	}

	protected function hash_equals($a, $b) {
		if (function_exists('random_bytes')) {
			$key = random_bytes(128);
		} else {
			$key = openssl_random_pseudo_bytes(128);
		}
		return hash_hmac('sha512', $a, $key) === hash_hmac('sha512', $b, $key);
	}

	/**
	 * Stretch the key using the PBKDF2 algorithm
	 *
	 * @see http://en.wikipedia.org/wiki/PBKDF2
	 *
	 * @param string $algo The algorithm to use
	 * @param string $key The key to stretch
	 * @param string $salt A random salt
	 * @param int $rounds The number of rounds to derive
	 * @param int $length The length of the output key
	 *
	 * @returns string The derived key.
	 */
	protected function pbkdf2($algo, $key, $salt, $rounds, $length) {
		$size = strlen(hash($algo, '', true));
		$len = ceil($length / $size);
		$result = '';
		for ($i = 1; $i <= $len; $i++) {
			$tmp = hash_hmac($algo, $salt . pack('N', $i), $key, true);
			$res = $tmp;
			for ($j = 1; $j < $rounds; $j++) {
				$tmp = hash_hmac($algo, $tmp, $key, true);
				$res ^= $tmp;
			}
			$result .= $res;
		}
		return substr($result, 0, $length);
	}

	/**
	 * Pad the data with a random char chosen by the pad amount.
	 *
	 * @param $data
	 * @return string
	 */
	protected function pad($data) {
		$length = $this->getKeySize();
		$padAmount = $length - strlen($data) % $length;
		if ($padAmount === 0) {
			$padAmount = $length;
		}
		return $data . str_repeat(chr($padAmount), $padAmount);
	}


	/**
	 * Unpad the the data
	 *
	 * @param $data
	 * @return bool|string
	 */
	protected function unpad($data) {
		$length = $this->getKeySize();
		$last = ord($data[strlen($data) - 1]);
		if ($last > $length) return false;
		if (substr($data, -1 * $last) !== str_repeat(chr($last), $last)) {
			return false;
		}
		return substr($data, 0, -1 * $last);
	}


	/**
	 * Encrypt a credential
	 *
	 * @param Credential|Entity|array $credential the credential to decrypt
	 * @return Credential|array
	 * @throws \Exception
	 */
	public function decryptCredential($credential) {
		return $this->handleCredential($credential, EncryptService::OP_DECRYPT);
	}

	/**
	 * Encrypt a credential
	 *
	 * @param Credential|array $credential the credential to encrypt
	 * @return Credential|array
	 * @throws \Exception
	 */
	public function encryptCredential($credential) {
		return $this->handleCredential($credential, EncryptService::OP_ENCRYPT);
	}


	private function extractKeysFromCredential($credential) {
		$userKey = '';
		$userSuppliedKey = '';
		if ($credential instanceof Credential) {
			$userSuppliedKey = $credential->getLabel();
			$sk = $credential->getSharedKey();
			$userKey = (isset($sk)) ? $sk : $credential->getUserId();
		}
		if (is_array($credential)) {
			$userSuppliedKey = $credential['label'];
			$userKey = (isset($credential['shared_key'])) ? $credential['shared_key'] : $credential['user_id'];
		}
		return array($userKey, $userSuppliedKey);
	}

	/**
	 * Handles the encryption / decryption of a credential
	 *
	 * @param Credential|array $credential the credential to encrypt
	 * @return Credential|array
	 * @throws \Exception
	 */
	private function handleCredential($credential, $service_function) {
		list($userKey, $userSuppliedKey) = $this->extractKeysFromCredential($credential);

		$key = $this->makeKey($userKey, $this->server_key, $userSuppliedKey);
		foreach ($this->encrypted_credential_fields as $field) {
			if ($credential instanceof Credential) {
				$field = str_replace(' ', '', str_replace('_', ' ', ucwords($field, '_')));
				$set = 'set' . $field;
				$get = 'get' . $field;
				$credential->{$set}($this->{$service_function}($credential->{$get}(), $key));
			}

			if (is_array($credential)) {
				$credential[$field] = $this->{$service_function}($credential[$field], $key);
			}
		}
		return $credential;
	}

	/**
	 * Encrypt a file
	 *
	 * @param File|array $file
	 * @return File|array
	 * @throws \Exception
	 */
	public function encryptFile($file) {
		return $this->handleFile($file, EncryptService::OP_ENCRYPT);
	}

	/**
	 * Decrypt a file
	 *
	 * @param File|Entity|array $file
	 * @return array|File
	 * @throws \Exception
	 */
	public function decryptFile($file) {
		return $this->handleFile($file, EncryptService::OP_DECRYPT);
	}

	/**
	 * Handles the encryption / decryption of a File
	 *
	 * @param File|array $file the credential to encrypt
	 * @return File|array
	 * @throws \Exception
	 */
	private function handleFile($file, $service_function) {
		$userKey = '';
		$userSuppliedKey = '';
		if ($file instanceof File) {
			$userSuppliedKey = $file->getSize();
			$userKey = md5($file->getMimetype());
		}

		if (is_array($file)) {
			$userSuppliedKey = $file['size'];
			$userKey = md5($file['mimetype']);
		}

		$key = $this->makeKey($userKey, $this->server_key, $userSuppliedKey);


		if ($file instanceof File) {
			$file->setFilename($this->{$service_function}($file->getFilename(), $key));
			$file->setFileData($this->{$service_function}($file->getFileData(), $key));
		}

		if (is_array($file)) {
			$file['filename'] = $this->{$service_function}($file['filename'], $key);
			$file['file_data'] = $this->{$service_function}($file['file_data'], $key);
		}

		return $file;
	}
}
