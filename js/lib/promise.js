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

function C_Promise(workload, context){
    this.parent = context;

    this.update = null; this.finally = null; this.error_function = null;
    this.then = function(callback){
        this.finally = callback;
        return this;
    };
    this.progress = function(callback){
        this.update = callback;
        return this;
    };
    this.error = function (callback){
        this.error_function = callback;
        return this;
    };
    this.call_then = function(data){
        if (this.finally !== null) this.finally(data);
    };
    this.call_progress = function(data){
        if (this.update !== null) this.update(data);
    };
    this.call_error = function(data){
        if(this.error_function !== null) this.error_function(data);
    };

    setTimeout(workload.bind(this), 100);
}