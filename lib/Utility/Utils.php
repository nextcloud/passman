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

namespace OCA\Passman\Utility;

use OCP\IUserManager;

class Utils {
    /**
     * Gets the unix epoch UTC timestamp
     * @return int
     */
	public static function getTime() {
		return (new \DateTime())->getTimestamp();
	}
	/**
	 * @return int the current unix time in milliseconds
	 */
	public static function getMicroTime() {
		return microtime(true);
	}

    /**
     * Generates a Globally Unique ID
     * @return string
     */
	public static function GUID() {
		if (function_exists('com_create_guid') === true)
		{
			return trim(com_create_guid(), '{}');
		}

		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

	/**
	 * @param string $uid
	 * @param IUserManager $userManager
	 * @return string
	 */
	public static function getNameByUid(string $uid, IUserManager $userManager){
		$u = $userManager->get($uid);
		return $u->getDisplayName();
	}

	/**
	 * @param string $dir
	 * @param array $results
	 * @return array|mixed
	 */
	public static function getDirContents(string $dir, &$results = array()){
		$files = scandir($dir);

		foreach($files as $value){
			$path = realpath($dir.DIRECTORY_SEPARATOR.$value);
			if(!is_dir($path)) {
				$results[] = $path;
			} else if($value != "." && $value != "..") {
				Utils::getDirContents($path, $results);
				$results[] = $path;
			}
		}
		return $results;
	}
}
