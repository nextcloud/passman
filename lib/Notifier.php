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

namespace OCA\Passman;

use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	protected IFactory $factory;

	public function __construct(IFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== 'passman') {
			// Not my app => throw
			throw new \InvalidArgumentException();
		}

		// Read the language from the notification
		$l = $this->factory->get('passman', $languageCode);

		switch ($notification->getSubject()) {
			// Deal with known subjects
			case 'credential_expired':
				$notification->setParsedSubject(
					(string)$l->t('Your credential "%s" expired, click here to update the credential.', $notification->getSubjectParameters())
				);

				// Deal with the actions for a known subject
				foreach ($notification->getActions() as $action) {
					switch ($action->getLabel()) {
						case 'remind':
							$action->setParsedLabel(
								(string)$l->t('Remind me later')
							);
							break;

						case 'ignore':
							$action->setParsedLabel(
								(string)$l->t('Ignore')
							);
							break;
					}

					$notification->addParsedAction($action);
				}
				return $notification;


			case 'credential_shared':
				$notification->setParsedSubject(
					(string)$l->t('%s shared "%s" with you. Click here to accept', $notification->getSubjectParameters())
				);

				// Deal with the actions for a known subject
				foreach ($notification->getActions() as $action) {
					switch ($action->getLabel()) {
						case 'decline':
							$action->setParsedLabel(
								(string)$l->t('Decline')
							);
							break;
					}

					$notification->addParsedAction($action);
				}
				return $notification;

			case 'credential_share_denied':
				$notification->setParsedSubject(
					(string)$l->t('%s has declined your share request for "%s".', $notification->getSubjectParameters())
				);
				return $notification;

			case 'credential_share_accepted':
				$notification->setParsedSubject(
					(string)$l->t('%s has accepted your share request for "%s".', $notification->getSubjectParameters())
				);
				return $notification;
			default:
				// Unknown subject => Unknown notification => throw
				throw new \InvalidArgumentException();
		}
	}

	/**
	 * Identifier of the notifier
	 *
	 * @return string
	 */
	public function getID(): string {
		return 'passman';
	}

	/**
	 * Human readable name describing the notifier
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->factory->get('passman')->t('Passwords');
	}
}
