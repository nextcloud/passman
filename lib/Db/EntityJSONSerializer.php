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
trait EntityJSONSerializer {
	public function serializeFields($properties) {
		$result = [];
		foreach ($properties as $property) {
			$result[$property] = $this->$property;
		}
		return $result;
	}
}