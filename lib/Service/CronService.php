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

use OCA\Passman\Activity;
use OCA\Passman\Db\Credential;
use OCA\Passman\Utility\Utils;
use OCP\DB\Exception;
use Psr\Log\LoggerInterface;

class CronService {

	public function __construct(
		private readonly CredentialService   $credentialService,
		private readonly LoggerInterface     $logger,
		private readonly Utils               $utils,
		private readonly NotificationService $notificationService,
		private readonly ActivityService     $activityService,
	) {
	}

	public function expireCredentials() {
		/** @var Credential[] $expired_credentials */
		$expired_credentials = $this->credentialService->getExpiredCredentials($this->utils->getTime());
		foreach ($expired_credentials as $credential) {
			try {
				$this->logger->debug($credential->getLabel() . ' is expired, checking notifications!', ['app' => 'passman']);
				if (!$this->notificationService->hasCredentialExpirationNotification($credential)) {
				$link = $this->credentialService->getDirectEditLink($credential);
					$this->logger->debug($credential->getLabel() . ' is expired, adding notification!', ['app' => 'passman']);
					$this->activityService->add(
						Activity::SUBJECT_ITEM_EXPIRED, [$credential->getLabel(), $credential->getUserId()],
						'', [],
						$link, $credential->getUserId(), Activity::TYPE_ITEM_EXPIRED);
					$this->notificationService->credentialExpiredNotification($credential, $link);
				}
			} catch (Exception $exception) {
				$this->logger->error('Error while creating a notification: ' . $exception->getMessage(), ['app' => 'passman']);
			}
		}
	}
}
