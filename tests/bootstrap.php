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


require_once __DIR__ . '/../../../tests/bootstrap.php';

require_once __DIR__ . '/../appinfo/autoload.php';
require_once __DIR__ . '/db/DatabaseHelperTest.php';


// Fix for "Autoload path not allowed: .../tests/lib/testcase.php"
\OC::$loader->addValidRoot(OC::$SERVERROOT . '/tests');
// Fix for "Autoload path not allowed: .../activity/tests/testcase.php"
\OC_App::loadApp('activity');
// Fix for "Autoload path not allowed: .../files/lib/activity.php"
\OC_App::loadApp('files');
// Fix for "Autoload path not allowed: .../files_sharing/lib/activity.php"
\OC_App::loadApp('files_sharing');

OC_Hook::clear();