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

use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\CredentialRevisionMapper;
use OCA\Passman\Db\ShareRequestMapper;
use OCA\Passman\Db\SharingACLMapper;
use OCA\Passman\Service\ActivityService;
use OCA\Passman\Service\CredentialRevisionService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\EncryptService;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\ShareService;
use OCA\Passman\Tests\PassmanTestCase;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Notification\IManager;

/**
 * Class InternalControllerTest
 *
 * @package OCA\Passman\Controller
 * @coversDefaultClass \OCA\Passman\Controller\InternalController
 */
class InternalControllerTest extends PassmanTestCase
{

    private InternalController $controller;
    private CredentialService $credentialService;

    public function setUp(): void
    {
        $config = $this->appContainer->get(IConfig::class);
        $request = $this->appContainer->get(IRequest::class);
        $notificationManager = $this->appContainer->get(IManager::class);
        $appManager = $this->appContainer->get(IAppManager::class);
        $settings = new SettingsService($this->userId, $config, self::APP_NAME);
        $encryptService = new EncryptService($settings, $config);

        $credentialRevisionMapper = $this->appContainer->get(CredentialRevisionMapper::class);
        $credentialRevisionService = new CredentialRevisionService($credentialRevisionMapper, $encryptService, $config);

        $credentialMapper = $this->appContainer->get(CredentialMapper::class);
        $sharingACLMapper = $this->appContainer->get(SharingACLMapper::class);
        $shareRequestMapper = $this->appContainer->get(ShareRequestMapper::class);

        $shareService = new ShareService(
            $sharingACLMapper,
            $shareRequestMapper,
            $credentialMapper,
            $credentialRevisionService,
            $encryptService,
            $notificationManager
        );
        $this->credentialService = new CredentialService(
            $credentialMapper,
            $sharingACLMapper,
            new ActivityService(),
            $shareService,
            $encryptService,
            $credentialRevisionService,
            $config
        );
        $this->controller = new InternalController(
            self::APP_NAME,
            $request,
            $this->userId,
            $this->credentialService,
            $config,
            $notificationManager,
            $appManager
        );
    }

    /**
     * @covers ::remind
     */
    public function testRemind()
    {
        $this->controller->remind(null);
        $this->assertTrue(true);
    }
    /**
     * @covers ::read
     */
    /*public function testRead() {
        $this->controller->read(null);
        $this->assertTrue(true);
    }*/

    /**
     * @covers ::getAppVersion
     */
    /*public function testGetAppVersion() {
        $this->assertTrue(true);
    }*/

    /**
     * @covers ::generatePerson
     */
    /*public function testGeneratePerson() {
        $this->assertTrue(true);
        //$result = $this->controller->generatePerson();
        //$this->assertTrue($result instanceof JSONResponse);
    }*/

    /**
     * @covers ::getSettings
     */
    /*public function testGetSettings() {
        $result = $this->controller->getSettings();
        $this->assertTrue($result instanceof JSONResponse);
    }*/

    /**
     * @covers ::saveSettings
     */
    /*public function testSaveSettings() {
        $result = $this->controller->saveSettings('test', 'test');
        $this->assertTrue(true);
    }*/

}
