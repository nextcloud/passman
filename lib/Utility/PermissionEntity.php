<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 2/10/16
 * Time: 13:27
 * This file is part of passman, licensed under AGPLv3
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