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

namespace OCA\Passman\Utility;

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
}