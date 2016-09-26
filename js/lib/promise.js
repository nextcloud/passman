/**
 * ownCloud/NextCloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Marcos Zuriaga <wolfi@wolfi.es>
 * @copyright Marcos Zuriarga 2016
 */

function C_Promise(workload){
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