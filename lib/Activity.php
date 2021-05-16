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

use OCP\IURLGenerator;
use OCP\L10N\IFactory;

class Activity implements \OCP\Activity\IExtension {
	const FILTER_PASSMAN = 'passman';
	const APP_NAME = 'passman';
	const TYPE_ITEM_ACTION = 'passman_item_action';
	const TYPE_ITEM_EXPIRED = 'passman_item_expired';
	const TYPE_ITEM_SHARED = 'passman_item_shared';
	const TYPE_ITEM_RENAMED = 'passman_item_renamed';

	const SUBJECT_ITEM_CREATED = 'item_created';
	const SUBJECT_ITEM_CREATED_SELF = 'item_created_self';
	const SUBJECT_ITEM_EDITED = 'item_edited';
	const SUBJECT_ITEM_EDITED_SELF = 'item_edited_self';
	const SUBJECT_APPLY_REV = 'item_apply_revision';
	const SUBJECT_APPLY_REV_SELF = 'item_apply_revision_self';
	const SUBJECT_ITEM_DELETED = 'item_deleted';
	const SUBJECT_ITEM_DELETED_SELF = 'item_deleted_self';
	const SUBJECT_ITEM_RECOVERED = 'item_recovered';
	const SUBJECT_ITEM_RECOVERED_SELF = 'item_recovered_self';
	const SUBJECT_ITEM_DESTROYED = 'item_destroyed';
	const SUBJECT_ITEM_DESTROYED_SELF = 'item_destroyed_self';
	const SUBJECT_ITEM_EXPIRED = 'item_expired';
	const SUBJECT_ITEM_SHARED = 'item_shared';
	const SUBJECT_ITEM_SHARE_RECEIVED = 'item_share_received';
	const SUBJECT_ITEM_SHARED_PUBLICLY = 'item_shared_publicly';
	const SUBJECT_ITEM_RENAMED = 'item_renamed';
	const SUBJECT_ITEM_RENAMED_SELF = 'item_renamed_self';


	protected $URLGenerator;
	protected $factory;

	public function __construct(IURLGenerator $URLGenerator, IFactory $factory) {
		$this->URLGenerator = $URLGenerator;
		$this->factory = $factory;
	}


	/**
	 * The extension can return an array of additional notification types.
	 * If no additional types are to be added false is to be returned
	 *
	 * @param string $languageCode
	 * @return array|false
	 */
	public function getNotificationTypes(string $languageCode) {
		$l = $this->factory->get(self::APP_NAME, $languageCode);
		return array(
			self::TYPE_ITEM_ACTION => $l->t('A Passman item has been created, modified or deleted'),
			self::TYPE_ITEM_EXPIRED => $l->t('A Passman item has expired'),
			self::TYPE_ITEM_SHARED => $l->t('A Passman item has been shared'),
			self::TYPE_ITEM_RENAMED => $l->t('A Passman item has been renamed')
		);
	}

	/**
	 * The extension can filter the types based on the filter if required.
	 * In case no filter is to be applied false is to be returned unchanged.
	 *
	 * @param array $types
	 * @param string $filter
	 * @return array|false
	 */
	public function filterNotificationTypes($types, $filter) {
		return $filter === self::FILTER_PASSMAN ? [self::TYPE_ITEM_ACTION, self::TYPE_ITEM_EXPIRED, self::TYPE_ITEM_SHARED, self::TYPE_ITEM_RENAMED] : $types;
	}

	/**
	 * For a given method additional types to be displayed in the settings can be returned.
	 * In case no additional types are to be added false is to be returned.
	 *
	 * @param string $method
	 * @return array|false
	 */
	public function getDefaultTypes($method) {
		if ($method === 'stream') {
			return array(
				self::TYPE_ITEM_ACTION,
				self::TYPE_ITEM_EXPIRED,
				self::TYPE_ITEM_SHARED,
				self::TYPE_ITEM_EXPIRED,
				self::TYPE_ITEM_RENAMED,
			);
		}
		if ($method === 'email') {
			return array(
				self::TYPE_ITEM_EXPIRED,
			);
		}
		return false;
	}

	/**
	 * The extension can translate a given message to the requested languages.
	 * If no translation is available false is to be returned.
	 *
	 * @param string $app
	 * @param string $text
	 * @param array $params
	 * @param boolean $stripPath
	 * @param boolean $highlightParams
	 * @param string $languageCode
	 * @return string|false
	 */
	public function translate($app, $text, $params, $stripPath, $highlightParams, $languageCode) {
		$l = $this->factory->get(self::APP_NAME, $languageCode);
		if ($app === self::APP_NAME) {
			switch ($text) {
				case self::SUBJECT_ITEM_CREATED:
					return $l->t('%1$s has been created by %2$s', $params);
				case self::SUBJECT_ITEM_CREATED_SELF:
					return $l->t('You created %1$s', $params);
				case self::SUBJECT_ITEM_EDITED:
					return $l->t('%1$s has been updated by %2$s', $params);
				case self::SUBJECT_ITEM_EDITED_SELF:
					return $l->t('You updated %1$s', $params);
				case self::SUBJECT_APPLY_REV:
					return $l->t('%2$s has revised %1$s to the revision of %3$s', $params);
				case self::SUBJECT_APPLY_REV_SELF:
					return $l->t('You reverted %1$s back to the revision of %3$s', $params);
				case self::SUBJECT_ITEM_RENAMED:
					return $l->t('%3$s has renamed %1$s to %2$s', $params);
				case self::SUBJECT_ITEM_RENAMED_SELF:
					return $l->t('You renamed %1$s to %2$s', $params);
				case self::SUBJECT_ITEM_DELETED:
					return $l->t('%1$s has been deleted by %2$s', $params);
				case self::SUBJECT_ITEM_DELETED_SELF:
					return $l->t('You deleted %1$s', $params);
				case self::SUBJECT_ITEM_RECOVERED:
					return $l->t('%1$s has been recovered by %2$s', $params);
				case self::SUBJECT_ITEM_RECOVERED_SELF:
					return $l->t('You recovered %1$s', $params);
				case self::SUBJECT_ITEM_DESTROYED:
					return $l->t('%1$s has been permanently deleted by %2$s', $params);
				case self::SUBJECT_ITEM_DESTROYED_SELF:
					return $l->t('You permanently deleted %1$s', $params);
				case self::SUBJECT_ITEM_EXPIRED:
					return $l->t('The password of %1$s has expired, renew it now.', $params);
				case self::SUBJECT_ITEM_SHARED:
					return $l->t('%1$s has been shared with %2$s', $params);
				case self::SUBJECT_ITEM_SHARE_RECEIVED:
					return $l->t('You received a share request for %1$s from %2$s', $params);
				case self::SUBJECT_ITEM_SHARED_PUBLICLY:
					return $l->t('%s has been shared with a link', $params);
			}
		}
		return false;
	}

	/**
	 * The extension can define the type of parameters for translation
	 *
	 * Currently known types are:
	 * * file => will strip away the path of the file and add a tooltip with it
	 * * username => will add the avatar of the user
	 *
	 * @param string $app
	 * @param string $text
	 * @return array|false
	 */
	public function getSpecialParameterList($app, $text) {
		if ($app === self::APP_NAME) {
			switch ($text) {
				case self::SUBJECT_ITEM_CREATED:
				case self::SUBJECT_ITEM_CREATED_SELF:
				case self::SUBJECT_ITEM_EDITED:
				case self::SUBJECT_ITEM_EDITED_SELF:
				case self::SUBJECT_ITEM_DELETED:
				case self::SUBJECT_ITEM_DELETED_SELF:
				case self::SUBJECT_ITEM_RECOVERED:
				case self::SUBJECT_ITEM_RECOVERED_SELF:
				case self::SUBJECT_ITEM_DESTROYED:
				case self::SUBJECT_ITEM_DESTROYED_SELF:
					return array(
						0 => 'passman',
						1 => 'username',
					);
				case self::SUBJECT_APPLY_REV:
				case self::SUBJECT_APPLY_REV_SELF:
					return array(
						0 => 'passman',
						1 => 'username',
						2 => '', //unknown
					);
				case self::SUBJECT_ITEM_EXPIRED:
				case self::SUBJECT_ITEM_RENAMED_SELF:
				case self::SUBJECT_ITEM_RENAMED:
				case self::SUBJECT_ITEM_SHARED:
				case self::SUBJECT_ITEM_SHARED_PUBLICLY:
					return array(
						0 => 'passman',
					);
			}
		}
		return false;
	}

	/**
	 * A string naming the css class for the icon to be used can be returned.
	 * If no icon is known for the given type false is to be returned.
	 *
	 * @param string $type
	 * @return string|false
	 */
	public function getTypeIcon($type) {
		switch ($type) {
			case self::TYPE_ITEM_ACTION:
			case self::TYPE_ITEM_EXPIRED:
				return 'icon-password';
			case self::TYPE_ITEM_SHARED:
				return 'icon-share';
			case self::TYPE_ITEM_RENAMED:
				return 'icon-rename';
		}
		return false;
	}

	/**
	 * The extension can define the parameter grouping by returning the index as integer.
	 * In case no grouping is required false is to be returned.
	 *
	 * @param array $activity
	 * @return integer|false
	 */
	public function getGroupParameter($activity) {
		return false;
	}

	/**
	 * The extension can define additional navigation entries. The array returned has to contain two keys 'top'
	 * and 'apps' which hold arrays with the relevant entries.
	 * If no further entries are to be added false is no be returned.
	 *
	 * @return array|false
	 */
	public function getNavigation() {
		$l = $this->factory->get(self::APP_NAME);
		return array(
			'top' => array(),
			'apps' => array(self::FILTER_PASSMAN =>
				array(
					'id' => 'passman',
					'name' => (string)$l->t('Passwords'),
					'url' => $this->URLGenerator->linkToRoute('activity.Activities.showList', ['filter' => self::FILTER_PASSMAN]),
				),
			),
		);
	}

	/**
	 * The extension can check if a customer filter (given by a query string like filter=abc) is valid or not.
	 *
	 * @param string $filterValue
	 * @return boolean
	 */
	public function isFilterValid($filterValue) {
		return $filterValue === self::FILTER_PASSMAN;
	}

	/**
	 * For a given filter the extension can specify the sql query conditions including parameters for that query.
	 * In case the extension does not know the filter false is to be returned.
	 * The query condition and the parameters are to be returned as array with two elements.
	 * E.g. return array('`app` = ? and `message` like ?', array('mail', 'ownCloud%'));
	 *
	 * @param string $filter
	 * @return array|false
	 */
	public function getQueryForFilter($filter) {
		if ($filter === self::FILTER_PASSMAN) {
			return [
				'(`app` = ?)',
				[self::APP_NAME],
			];
		}
		return false;

	}
}
