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
class EncryptService {

	/**
	 * Supported cipher algorithms accompanied by their key/block sizes in bytes
	 *
	 * OpenSSL has no equivalent of mcrypt_get_key_size() and mcrypt_get_block_size() hence sizes stored here.
	 *
	 * @var array
	 */
	protected $supportedAlgos = array(
		'aes-256' => array('name' => 'AES-256', 'keySize' => 32, 'blockSize' => 32),
		'bf' => array('name' => 'BF', 'keySize' => 16, 'blockSize' => 8),
		'des' => array('name' => 'DES', 'keySize' => 7, 'blockSize' => 8),
		'des-ede3' => array('name' => 'DES-EDE3', 'keySize' => 21, 'blockSize' => 8), // 3 different 56-bit keys
		'cast5' => array('name' => 'CAST5', 'keySize' => 16, 'blockSize' => 8),
	);

	/**
	 * Supported encryption modes
	 *
	 * @var array
	 */
	protected $supportedModes = array(
		'cbc' => 'CBC',
	);

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
	 * @var string $cipher The mcrypt cipher to use for this instance
	 */
	protected $cipher = '';

	/**
	 * @var int $mode The mcrypt cipher mode to use
	 */
	protected $mode = '';

	/**
	 * @var int $rounds The number of rounds to feed into PBKDF2 for key generation
	 */
	protected $rounds = 100;

	/**
	 * Constructor!
	 *
	 * @param string $cipher The MCRYPT_* cypher to use for this instance
	 * @param int $mode The MCRYPT_MODE_* mode to use for this instance
	 * @param int $rounds The number of PBKDF2 rounds to do on the key
	 */
	public function __construct($cipher, $mode, $rounds = 100) {
		$this->cipher = $cipher;
		$this->mode = $mode;
		$this->rounds = (int)$rounds;
	}

	/**
	 * Get the maximum key size for the selected cipher and mode of operation
	 *
	 * @return int Value is in bytes
	 */
	public function getKeySize() {
		return $this->supportedAlgos[$this->cipher]['keySize'];
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

		if (!EncryptService::hash_equals(hash_hmac('sha512', $enc, $macKey, true), $mac)) {
			return false;
		}

		$dec = openssl_decrypt($enc, $this->cipher . '-' . $this->mode, $cipherKey, true, $iv);
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
		$salt = openssl_random_pseudo_bytes(128);
		list ($cipherKey, $macKey, $iv) = EncryptService::getKeys($salt, $key);
		$data = EncryptService::pad($data);
		$enc = openssl_encrypt($data, $this->cipher . '-' . $this->mode, $cipherKey, true, $iv);


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
		$ivSize = openssl_cipher_iv_length($this->cipher . '-' . $this->mode);
		$keySize = openssl_cipher_iv_length($this->cipher . '-' . $this->mode);
		$length = 2 * $keySize + $ivSize;

		$key = EncryptService::pbkdf2('sha512', $key, $salt, $this->rounds, $length);

		$cipherKey = substr($key, 0, $keySize);
		$macKey = substr($key, $keySize, $keySize);
		$iv = substr($key, 2 * $keySize);
		return array($cipherKey, $macKey, $iv);
	}

	function hash_equals($a, $b) {
		$key = openssl_random_pseudo_bytes(128);
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
		if ($padAmount == 0) {
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
}