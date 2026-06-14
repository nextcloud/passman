<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @copyright 2026 Timo Triebensky (timo@binsky.org)
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

use OCA\Passman\AppInfo\Application;
use OCP\Activity\IManager;
use OCP\Server;


class ActivityService {

	private $manager;

	public function __construct() {
			$this->manager = Server::get(IManager::class);
	}

	/**
	 * Create a new activity
	 * @param $subject string Subject of the activity
	 * @param $subjectParams array
	 * @param $message string
	 * @param $messageParams array
	 * @param $link string
	 * @param $user string|null
	 * @param $type string
	 * @return array
	 */
	public function add(
		string  $subject,
		array   $subjectParams = [],
		string  $message = '',
		array   $messageParams = [],
		string  $link = '',
		?string $user = null,
		string $type = ''
	) {
		if($user) {
			$activity = $this->manager->generateEvent();
			$activity->setType($type);
			$activity->setApp(Application::APP_ID);
			$activity->setSubject($subject, $subjectParams);
			$activity->setLink($link);
			$activity->setAffectedUser($user);
			$activity->setAuthor($user);
			$activity->setTimestamp(time());
			$activity->setMessage($message, $messageParams);
		}
		return ['success'=>'ok'];
	}
}
