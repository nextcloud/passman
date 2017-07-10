#Passman
Passman is a full featured password manager.

[![Build Status](https://travis-ci.org/nextcloud/passman.svg?branch=master)](https://travis-ci.org/nextcloud/passman)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/749bb288c9fd4592a73056549d44a85e)](https://www.codacy.com/app/brantje/passman?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=nextcloud/passman&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/749bb288c9fd4592a73056549d44a85e)](https://www.codacy.com/app/brantje/passman?utm_source=github.com&utm_medium=referral&utm_content=nextcloud/passman&utm_campaign=Badge_Coverage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nextcloud/passman/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/passman/?branch=master)


## Contents
* [Screenshots](https://github.com/nextcloud/passman#Screenshots) 
* [Features](https://github.com/nextcloud/passman#features) 
* [External apps](https://github.com/nextcloud/passman#external-apps)
* [Security](https://github.com/nextcloud/passman#security)
  * [Password generation](https://github.com/nextcloud/passman#password-generation)
  * [Storing credentials](https://github.com/nextcloud/passman#storing-credentials)
* [API](https://github.com/nextcloud/passman#api)
* [Docker](https://github.com/nextcloud/passman#docker)
* [Maintainers](https://github.com/nextcloud/passman#main-developers)
* [Contributors](https://github.com/nextcloud/passman#contributors)




##Screenshots
![Logged in to vault](http://i.imgur.com/ciShQZg.png)   

![Credential selected](http://i.imgur.com/3tENldT.png)   

![Edit credential](http://i.imgur.com/Iwm3hUe.png)   

![Password tool](http://i.imgur.com/ZYkN70r.png)

For more screenshots: [Click here](http://imgur.com/a/giKVt)


## Features:
- Vaults
- Vault key is never sent to the server
- Credentials are stored with 256 bit AES (see [security](https://github.com/nextcloud/passman#security))
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
  - EnPass
  - [ocPasswords](https://github.com/fcturner/passwords)
  

For a demo of this app visit [https://demo.passman.cc](https://demo.passman.cc)

## Tested on
- NextCloud 10 / 11
- ownCloud 9.1+


## External apps
- [Firefox / chrome extension](https://github.com/nextcloud/passman-webextension)
- [Android app](https://github.com/nextcloud/passman-android)


## Supported databases
- SQL Lite*
- MySQL / MariaDB*

*Tested on travis

Untested databases:
- pgsql

## Security

### Password generation
Passman features a build in password generator.
Not it only generates passwords, but it also measures their strength using [zxcvbn](https://github.com/dropbox/zxcvbn).   
![](http://i.imgur.com/2qVBUfM.png)   

Generate passwords as you like   
![](http://i.imgur.com/jcRicOV.png)   
Passwords are generated using the random functions from `sjcl`.


### Storing credentials
All passwords are encrypted client side using [sjcl](https://github.com/bitwiseshiftleft/sjcl) which uses AES-256 bit.
Users supply a vault key which is feed into sjcl as encryption key.
After the credentials are encrypted they are send to the server, there they will be encrypted again.
This time using the following routine:
- A key is generated using `passwordsalt` and `secret` from config.php *so back those up*
- Then the key is [stretched](http://en.wikipedia.org/wiki/Key_stretching) using [Password-Based Key Derivation Function 2](http://en.wikipedia.org/wiki/PBKDF2) (PBKDF2).
- [Encrypt-then-MAC](http://en.wikipedia.org/wiki/Authenticated_encryption#Approaches_to_Authenticated_Encryption) (EtM) is used for ensuring the authenticity of the encrypted data.
- Uses openssl with the `aes-256-cbc` ciper.
- [Initialization vector](http://en.wikipedia.org/wiki/Initialization_vector) (IV) is hidden
- [Double Hash-based Message Authentication Code](http://en.wikipedia.org/wiki/Hash-based_message_authentication_code) (HMAC) is applied for verification of the source data.


### Sharing credentials.
Passman allows users to share passwords (this can be turned off by an administrator). 



## API 
For developers Passman offers an [api](https://github.com/nextcloud/passman/wiki/API).

## Support Passman
Passman is open source, and we would gladly accept a beer (or pizza!)   
Please consider donating
- [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6YS8F97PETVU2)
- [Patreon](https://www.patreon.com/user?u=4833592)
- bitcoin: 1H2c5tkGX54n48yEtM4Wm4UrAGTW85jQpe

## Code reviews
If you have any improvements regarding our code.
Please do the following
- Clone us
- Make your edits
- Add your name to the contributors 
- Send a [PR](https://github.com/nextcloud/passman/pulls)

Or if you're feeling lazy, create an issue, and we'll think about it.

## Docker
To run Passman with [Docker](https://www.docker.com/) you can use `docker run  -p 8080:80 -p 8443:443 brantje/passman`   
You have to supply your own SSL certs.   
Example:   
`docker run -p 8080:80 -p 8443:443 -v /directory/cert.pem:/data/ssl/cert.pem -v /directory/cert.key:/data/ssl/cert.key brantje/passman`


## Development
Passman uses a single `.js` file for the templates. This gives the benefit that we don't need to request every template with XHR.   
For CSS we use SASS so you need ruby and sass installed.  
`templates.js` and the CSS are built with `grunt`.
To watch for changes use `grunt watch`
To run the unit tests install phpunit globally, and setup the environment variables on the `launch_phpunit.sh` script then just run that script, any arguments passed to this script will be forwarded to phpunit.

## Main developers
- Brantje
- Animalillo

## Contributors
Add yours when creating a [pull request](https://help.github.com/articles/creating-a-pull-request/)!
- None


## FAQ
**Are you adding something to check if malicious code is executing on the browser?**   
No, because malicious code could edit the functions that check for malicious code.
