<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Passman\BackgroundJob;

use OC\BackgroundJob\TimedJob;
use \OCA\Passman\AppInfo\Application;
use OCP\IConfig;

/**
 * Class ExpireCredentials
 *
 * @package OCA\Passman\BackgroundJob
 */
class ExpireCredentials extends TimedJob {
	/** @var IConfig */
	protected $config;

	/**
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		// Run once per minute
		$this->setInterval(60);
		$this->config = $config;
	}

	protected function run($argument) {
		$app = new Application();
		$container = $app->getContainer();
		$container->query('CronService')->expireCredentials();
	}
}
