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

namespace OCA\Passman\BackgroundJob;

use OC\BackgroundJob\TimedJob;
use OCA\Passman\Service\CronService;
use OCP\IConfig;

/**
 * Class ExpireCredentials
 *
 * @package OCA\Passman\BackgroundJob
 */
class ExpireCredentials extends TimedJob {

	protected IConfig $config;
	private CronService $cronService;

	/**
	 * ExpireCredentials constructor.
	 * @param IConfig $config
	 * @param CronService $cronService
	 */
	public function __construct(IConfig $config, CronService $cronService) {
		// Run once per minute
		$this->setInterval(60);
		$this->config = $config;
		$this->cronService = $cronService;
	}

	protected function run($argument) {
		$this->cronService->expireCredentials();
	}
}
