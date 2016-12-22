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

namespace OCA\Passman\Utility;


use OCP\AppFramework\Db\Entity;

class PermissionEntity extends Entity {
    CONST READ  =   0b00000001;
    CONST WRITE =   0b00000010;
    CONST FILES =   0b00000100;
    CONST HISTORY = 0b00001000;
    CONST OWNER =   0b10000000;

    /**
     * Checks wether a user matches one or more permissions at once
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission) {
        $tmp = $this->getPermissions();
        $tmp = $tmp & $permission;
        return $tmp === $permission;
    }

    /**
     * Adds the given permission or permissions set to the user current permissions
     * @param $permission
     */
    public function addPermission($permission) {
        $tmp = $this->getPermissions();
        $tmp = $tmp | $permission;
        $this->setPermissions($tmp);
    }

    /**
     * Takes the given permission or permissions out from the user
     * @param $permission
     */
    public function removePermission($permission) {
        $tmp = $this->getPermissions();
        $tmp = $tmp & ~$permission;
        $this->setPermissions($tmp);
    }
}