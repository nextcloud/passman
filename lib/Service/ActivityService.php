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

use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Passman\Db\FileMapper;


class ActivityService {

	private $manager;

	public function __construct() {
			$this->manager = \OC::$server->getActivityManager();
	}

	/**
	 * @subject = One of these: item_created, item_edited, item_apply_revision
	 *                          item_deleted, item_recovered, item_destroyed,
	 *                          item_expired, item_shared
	 *
	 *
	 *
	 *
	 * @subjectParams =  Subject     | Subject params
	 *                  item_created = array($itemName,$user)
	 *                  item_edited = array($itemName,$user)
	 *                  item_apply_revision = array($itemName,$user,$revision);
	 *                  item_deleted = array($itemName,$user)
	 *                  item_recovered = array($itemName,$user)
	 *                  item_destroyed = array($itemName,$user)
	 *                  item_expired = array($itemName)
	 *                  item_shared = array($itemName)
	 * @message = Custom message (not needed)
	 * @messageParams = Message params (not needed)
	 * @link = will be -> <ownCloud>/apps/activity/$link
	 * @user = Target user
	 * @type = Can be passman_password or passman_password_shared
	 * @priority = Int -> [10,20,30,40,50]
	 */
	public function add($subject,$subjectParams=array(),
						$message='',$messageParams=array(),
						$link='',$user=null,$type='') {
		$activity = $this->manager->generateEvent();
		$activity->setType($type);
		$activity->setApp('passman');
		$activity->setSubject($subject, $subjectParams);
		$activity->setLink($link);
		$activity->setAffectedUser($user);
		$activity->setAuthor($user);
		$activity->setTimestamp(time());
		$activity->setMessage($message, $messageParams);
		print_r($this->manager->publish($activity));
		return array('success'=>'ok');
	}
}