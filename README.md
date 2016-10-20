#Currently in BETA
#Passman

[![Build Status](https://travis-ci.org/nextcloud/passman.svg?branch=master)](https://travis-ci.org/nextcloud/passman)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nextcloud/passman/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/passman/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nextcloud/passman/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/passman/?branch=master)


Passman is a full featured password manager.
Features:
- Vaults
- Vault key is never sent to the server
- Credentials are stored with 256 bit AES
- Ability to add custom fields to credentials
- Built-in OTP(One Time Password) generator
- Password analyzer
- Share passwords internally and via link in a secure manner.
- Import from various password managers:
  - KeePass
  - LastPass
  - DashLane
  - ZOHO
  - Clipperz.is


## Tested on
- NextCloud 10+
- ownCloud 9.1+


## Supported databases
- SQL Lite*
- MySQL / MariaDB*

*Tested on travis

Untested databases:
- pgsql


##Screenshots
![Logged in to vault](http://i.imgur.com/ciShQZg.png)   

![Credential selected](http://i.imgur.com/3tENldT.png)   

![Edit credential](http://i.imgur.com/Iwm3hUe.png)   

![Password tool](http://i.imgur.com/ZYkN70r.png)

For more screenshots: [Click here](http://imgur.com/a/giKVt)

## Code reviews
If you have any improvements regarding our code.
Please do the following
- Clone us
- Make your edits
- Add your name to the contributors 
- Send a PR   

Or if you're feeling lazy, create an issue, and we'll think about it.

## Docker
To run passman with docker you can use `docker run  -p 8080:80 -p 8443:443 brantje/passman`
SSL certificates are not shipped by default, you have to mount them:   
Example:   
`-v etc/ssl/certs/ssl-cert-snakeoil.pem:/etc/ssl/certs/ssl-cert-snakeoil.pem -v /etc/ssl/private/ssl-cert-snakeoil.key:/etc/ssl/private/ssl-cert-snakeoil.key`


## Development
Passman uses a single `.js` file for the templates. This gives the benefit that we don't need to request every template with XHR.   
For CSS we use SASS so you need ruby and sass installed.  
`templates.js` and the CSS are build width `grunt`.
To watch for changes use `grunt watch`
To run the unit tests install phpunit globally, and stup the envioronment variables on the `launch_phpunit.sh` script then just run that script any argumetns passed to this script will be forwarded to phpunit.

## Main developers
- Brantje
- Animalillo

## Contributors
Add yours when creating a pull request!
- None


## FAQ
**Are you adding something to check if malicious code is executing on the browser?**   
No, because malitous code could edit the functions that check for malicious code.