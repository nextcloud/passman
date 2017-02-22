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

namespace OCA\Passman\Controller;

use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\DeleteVaultRequestService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\VaultService;
use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\JSONResponse;

/**
 * Class VaultControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass \OCA\Passman\Controller\VaultController
 */
class VaultControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;
	private $userId = 'example';
	private $credentialService;
	private $vaultService;
	private $deleteVaultRequestService;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->vaultService = $this->createMock(VaultService::class);
		$this->credentialService = $this->createMock(CredentialService::class);
		$this->settingsService = $this->createMock(SettingsService::class);
		$this->deleteVaultRequestService = $this->createMock(DeleteVaultRequestService::class);

		$this->controller = new VaultController(
			'passman', $request, $this->userId, $this->vaultService, $this->credentialService, $this->deleteVaultRequestService,  $this->settingsService
		);
	}

	/**
	 * @covers ::listVaults
	 */
	public function testListVaults() {
		$result = $this->controller->listVaults();
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::create
	 */
	public function testCreate() {
		$result = $this->controller->create('My test vault');
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * @covers ::get
	 */
	public function testGet() {
		$result = $this->controller->get('');
		$this->assertTrue($result instanceof JSONResponse);
	}

	/**
	 * Just test the method
	 * @see http://stackoverflow.com/questions/27511593/how-to-phpunit-test-a-method-with-no-return-value
	 *
	 * @covers ::update
	 */
	public function testUpdate() {
		$this->controller->update('6AD30804-BFFC-4EFC-97F8-20A126FA1709', 'testname' ,null);
		$this->assertTrue(true);
	}

	/**
	 * Just test the method
	 * @see http://stackoverflow.com/questions/27511593/how-to-phpunit-test-a-method-with-no-return-value
	 *
	 * @covers ::updateSharingKeys
	 */
	public function testUpdateSharingKeys() {
		$this->controller->updateSharingKeys('6AD30804-BFFC-4EFC-97F8-20A126FA1709', null ,null);
		$this->assertTrue(true);
	}

	/**
	 * @covers ::delete
	 */
	public function testDelete() {
		$result = $this->controller->delete('');
		$this->assertTrue($result instanceof JSONResponse);
	}
}