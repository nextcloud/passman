/**
 * Created by Marcos Zuriaga on 3/10/16.
 * This file is part of passman, licensed under AGPLv3
 */

angular.module('passmanApp').factory('SharingACL', function(){
    function ACL(acl_permission){
        var permission = acl_permission;
        var permissions = {
            READ: 0x01,
            WRITE: 0x03,
            OWNER: 0x80,
        };
    }

    /**
     * Checks if a user has the given permission/s
     * @param permission
     * @returns {boolean}
     */
    ACL.prototype.hasPermission = function(permission){
        return this.permission == (this.permission & permission);
    };

    /**
     * Adds a permission to a user, leaving any other permissions intact
     * @param permission
     */
    ACL.prototype.addPermission = function(permission){
        this.permission = this.permission | permission;
    };

    /**
     * Removes a given permission from the item, leaving any other intact
     * @param permission
     */
    ACL.prototype.removePermission = function(permission){
        this.permission = this.permission & !permission;
    };

    return ACL;
});