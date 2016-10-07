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
	public function add($subject, $subjectParams = array(),
						$message = '', $messageParams = array(),
						$link = '', $user = null, $type = '') {
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