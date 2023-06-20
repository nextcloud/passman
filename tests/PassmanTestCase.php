<?php

namespace OCA\Passman\Tests;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use Test\TestCase;

class PassmanTestCase extends TestCase
{
    public const APP_NAME = 'passman';
    public App $app;
    public IAppContainer $appContainer;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->app = new App(self::APP_NAME);
        $this->appContainer = $this->app->getContainer();
    }
}
