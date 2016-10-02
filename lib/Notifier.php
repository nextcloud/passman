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

namespace OCA\Passman;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	protected $factory;

	public function __construct(\OCP\L10N\IFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 */
	public function prepare(INotification $notification, $languageCode) {
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
					(string) $l->t('Your credential "%s" expired, click here to update the credential.', $notification->getSubjectParameters())
				);

				// Deal with the actions for a known subject
				foreach ($notification->getActions() as $action) {
					switch ($action->getLabel()) {
						case 'remind':
							$action->setParsedLabel(
								(string) $l->t('Remind me later')
							);
							break;

						case 'ignore':
							$action->setParsedLabel(
								(string) $l->t('Ignore')
							);
							break;
					}

					$notification->addParsedAction($action);
				}
				return $notification;
				break;

			case 'credential_shared':
				$notification->setParsedSubject(
					(string) $l->t('%s shared "%s" with you. Click here to accept', $notification->getSubjectParameters())
				);

				// Deal with the actions for a known subject
				foreach ($notification->getActions() as $action) {
					switch ($action->getLabel()) {
						case 'decline':
							$action->setParsedLabel(
								(string) $l->t('Decline')
							);
							break;
					}

					$notification->addParsedAction($action);
				}
				return $notification;
				break;

			case 'credential_share_denied':
				$notification->setParsedSubject(
					(string) $l->t('%s has declined your share request for "%s".', $notification->getSubjectParameters())
				);
				return $notification;
				break;

			case 'credential_share_accepted':
				$notification->setParsedSubject(
					(string) $l->t('%s has accepted your share request for "%s".', $notification->getSubjectParameters())
				);
				return $notification;
				break;
			default:
				// Unknown subject => Unknown notification => throw
				throw new \InvalidArgumentException();
		}
	}
}