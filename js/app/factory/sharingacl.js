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

(function () {
	'use strict';
	/**
	 * Created by Marcos Zuriaga on 3/10/16.
	 * This file is part of passman, licensed under AGPLv3
	 */

	angular.module('passmanApp').factory('SharingACL', function () {
		function ACL (acl_permission) {
			this.permission = acl_permission;
		}

		ACL.prototype.permissions = {
			READ: 0x01,
			WRITE: 0x02,
			FILES: 0x04,
			HISTORY: 0x08,
			OWNER: 0x80,
		};
		/**
		 * Checks if a user has the given permission/s
		 * @param permission
		 * @returns {boolean}
		 */
		ACL.prototype.hasPermission = function (permission) {
			return permission === (this.permission & permission);
		};

		/**
		 * Adds a permission to a user, leaving any other permissions intact
		 * @param permission
		 */
		ACL.prototype.addPermission = function (permission) {
			this.permission = this.permission | permission;
		};

		/**
		 * Removes a given permission from the item, leaving any other intact
		 * @param permission
		 */
		ACL.prototype.removePermission = function (permission) {
			this.permission = this.permission & ~permission;
		};

		ACL.prototype.togglePermission = function (permission) {
			this.permission ^= permission;
		};

		ACL.prototype.getAccessLevel = function () {
			return this.permission;
		};

		return ACL;
	});
}());