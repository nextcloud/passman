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

use OCA\Passman\Db\DeleteVaultRequest;
use OCA\Passman\Db\DeleteVaultRequestMapper;
use OCP\AppFramework\Db\Entity;


class DeleteVaultRequestService {

	private DeleteVaultRequestMapper $deleteVaultRequestMapper;

	public function __construct(DeleteVaultRequestMapper $deleteVaultRequestMapper) {
		$this->deleteVaultRequestMapper = $deleteVaultRequestMapper;
	}

	/**
	 *  Create a new DeleteVaultRequest
	 *
	 * @param $request DeleteVaultRequest
	 * @return DeleteVaultRequest
	 */
	public function createRequest(DeleteVaultRequest $request) {
		return $this->deleteVaultRequestMapper->insert($request);
	}

	/**
	 * Create a new DeleteVaultRequest
	 *
	 * @return Entity[]
	 */
	public function getDeleteRequests() {
		return $this->deleteVaultRequestMapper->getDeleteRequests();
	}

	/**
	 *  Create a new DeleteVaultRequest
	 *
	 * @param $vault_guid string The vault guid
	 * @return bool | Entity
	 */
	public function getDeleteRequestForVault(string $vault_guid) {
		try {
			return $this->deleteVaultRequestMapper->getDeleteRequestsForVault($vault_guid);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 *  Create a new DeleteVaultRequest
	 *
	 * @param $req DeleteVaultRequest
	 */
	public function removeDeleteRequestForVault(DeleteVaultRequest $req) {
		$this->deleteVaultRequestMapper->removeDeleteVaultRequest($req);
	}
}
