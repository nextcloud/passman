/**
 * ownCloud/NextCloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Marcos Zuriaga <wolfi@wolfi.es>
 * @copyright Marcos Zuriarga 2015
 */
var CRYPTO = {    // Global variables of the object:
    paranoia_level: null,

    PASSWORD : {
        /**
         * Callback will be called once the password its generated, it should accept one parameter, and the parameter will be the key (
         *  CRYPTO.PASSWORD.generate(100, function(password){
         *      console.log("The generated password is: " + password);
         *      // Do more stuff here
         *  }, function (current_percentage){
         *      console.log("The current password generation progress it's: " + current_percentage + "%");
         *     // Do real stuff here, update a progressbar, etc.
         *  }
         *  );
         * )
         * @param length    The minium length of the generated password (it generates in packs of 4 characters,
         * so it can end up being up to 3 characters longer)
         * @param callback  The function to be called after the password generation its done
         * @param progress  The process of the generation, optional, called each 4 characters generated.
         */
        generate : function (length, callback, progress, start_string) {
            if (!sjcl.random.isReady(paranoia_level)) {
                setTimeout(this.generate(length, callback, progress, start_string), 500);
                return;
            }

            if (start_string == null) start_string = "";
            if (start_string.length < length) {
                start_string += CRYPTO.RANDOM.getRandomASCII();
                if (progress != null) progress(start_string.length / length * 100);
            }
            else {
                callback(start_string);
                if (progress != null) progress(100);
                return;
            }

            setTimeout(this.generate(length, callback, progress, start_string), 100);
        },

        logRepeatedCharCount: function (str) {
            var chars = [];

            for (i = 0; i < str.length; i++) {
                chars[str.charAt(i)] = (chars[str.charAt(i)] == null) ? 0 : chars[str.charAt(i)] + 1;
            }
            return chars;
        },
    },

    RANDOM: {
        /**
         * Returns a random string of 4 characters length
         */
        getRandomASCII : function () {
            // console.warn(paranoia_level);

            var ret = "";
            while (ret.length < 4) {
                var int = sjcl.random.randomWords(1, paranoia_level);
                int = int[0];

                var tmp = this._isASCII((int & 0xFF000000) >> 24);
                if (tmp) ret += tmp;

                tmp = this._isASCII((int & 0x00FF0000) >> 16);
                if (tmp) ret += tmp;

                tmp = this._isASCII((int & 0x0000FF00) >> 8);
                if (tmp) ret += tmp;

                tmp = this._isASCII(int & 0x000000FF);
                if (tmp)  ret += tmp;
            }

            return ret;
        },

        /**
         * Checks whether the given data it's an ascii character, returning the corresponding character; returns false otherwise
         *
         * @param data
         * @returns {string}
         * @private
         */
        _isASCII : function (data) {
            return (data > 31 && data < 127) ? String.fromCharCode(data) : false;
        }
    },

    /**
     * Initializes the random and other cryptographic engines needed for this library to work
     * The default paranoia, in case no paranoia level it's provided, it's 10 (1024).
     * The higher paranoia level allowed by sjcl.
     *
     * PARANOIA_LEVELS:
     *  0 = 0
     *  1 = 48
     *  2 = 64
     *  3 = 96
     *  4 = 128
     *  5 = 192
     *  6 = 256
     *  7 = 384
     *  8 = 512
     *  9 = 768
     *  10 = 1024
     *
     * @param default_paranoia (0-10 integer)
     */
    initEngines : function (default_paranoia) {
        paranoia_level = default_paranoia || 10;

        sjcl.random.setDefaultParanoia(this.paranoia_level);
        sjcl.random.startCollectors();

        console.warn('Crypto stuff initialized');
    }
};
CRYPTO.initEngines();
