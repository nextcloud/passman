# Passman
Passman is a full featured password manager.

[![Build Status](https://travis-ci.org/nextcloud/passman.svg?branch=master)](https://travis-ci.org/nextcloud/passman)
[![Docker Automated buid](https://img.shields.io/docker/build/brantje/passman.svg)](hub.docker.com/r/brantje/passman/)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/749bb288c9fd4592a73056549d44a85e)](https://www.codacy.com/app/brantje/passman?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=nextcloud/passman&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/749bb288c9fd4592a73056549d44a85e)](https://www.codacy.com/app/brantje/passman?utm_source=github.com&utm_medium=referral&utm_content=nextcloud/passman&utm_campaign=Badge_Coverage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nextcloud/passman/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nextcloud/passman/?branch=master)

## Join us!
Visit the [“Passman General Talk” Telegram Group](https://t.me/passman_general) to participate in all sorts of topical discussions about Passman and its apps!

## Contents
  * [Screenshots](https://github.com/nextcloud/passman#Screenshots) 
  * [Features](https://github.com/nextcloud/passman#features) 
  * [External apps](https://github.com/nextcloud/passman#external-apps)
  * [Security](https://github.com/nextcloud/passman#security)
    * [Password generation](https://github.com/nextcloud/passman#password-generation)
    * [Storing credentials](https://github.com/nextcloud/passman#storing-credentials)
  * [Support passman](https://github.com/nextcloud/passman#support-passman)
  * [Development](https://github.com/nextcloud/passman#development)
  * [API](https://github.com/nextcloud/passman#api)
  * [Docker](https://github.com/nextcloud/passman#docker)
  * [Maintainers](https://github.com/nextcloud/passman#main-developers)
  * [Contributors](https://github.com/nextcloud/passman#contributors)

## Screenshots
![Logged in to vault](http://i.imgur.com/ciShQZg.png)   

![Credential selected](http://i.imgur.com/3tENldT.png)   

![Edit credential](http://i.imgur.com/Iwm3hUe.png)   

![Password tool](http://i.imgur.com/ZYkN70r.png)

For more screenshots: [Click here](http://imgur.com/a/giKVt)

## Features:
  * Multiple vaults
  * Vault keys are never sent to the server
  * 256-bit AES-encrypted credentials (see [security](https://github.com/nextcloud/passman#security))
  * User-defined custom credentials fields
  * Built-in OTP (One Time Password) generator
  * Password analyzer
  * Securely share passwords internally and via link
  * Import from various password managers:
    - KeePass
    - LastPass
    - DashLane
    - ZOHO
    - Clipperz.is
    - EnPass
    - [ocPasswords](https://github.com/fcturner/passwords)
  
Try a Passman demo [here](https://demo.passman.cc).

## Tested on
- Nextcloud 14

For older Versions see the [Releases Tab](https://github.com/nextcloud/passman/releases)

## External apps
  * [Firefox / chrome extension](https://github.com/nextcloud/passman-webextension)
  * [Android app](https://github.com/nextcloud/passman-android)

## Database Compatibility

|   | Supported | Tested | Untested |
| :--- | :---: | :---: | :---: |
| SQL Lite | • |   |   |
| MySQL / MariaDB | • |   |   |
| travis |   | • |   |
| pgsql | • |   |   |

## Security

### Password generation
Passman can generate passwords *and* measure their strength using [zxcvbn](https://github.com/dropbox/zxcvbn).   
![](http://i.imgur.com/2qVBUfM.png)   

Generate passwords as you like   
![](http://i.imgur.com/jcRicOV.png)   
Passwords are generated using `sjcl` randomization.

### Storing credentials
All passwords are encrypted client side with [sjcl](https://github.com/bitwiseshiftleft/sjcl) using 256-bit AES.
You supply a vault key which sjcl uses to encrypt your credentials. Your encrypted credentials are then sent to the server and encrypted yet again using the following routine:
  * A key is generated using `passwordsalt` and `secret` from config.php *(so back those up)*.
  * The key is [stretched](http://en.wikipedia.org/wiki/Key_stretching) using [Password-Based Key Derivation Function 2](http://en.wikipedia.org/wiki/PBKDF2) (PBKDF2).
  * [Encrypt-then-MAC](http://en.wikipedia.org/wiki/Authenticated_encryption#Approaches_to_Authenticated_Encryption) (EtM) is used to ensure encrypted data authenticity.
  * Uses openssl with the `aes-256-cbc` cipher.
  * [Initialization vector](http://en.wikipedia.org/wiki/Initialization_vector) (IV) is hidden.
  * [Double Hash-based Message Authentication Code](http://en.wikipedia.org/wiki/Hash-based_message_authentication_code) (HMAC) is applied for source data verification.

### Sharing credentials
Passman allows users to share passwords. *(Administrators may disable this feature.)*

## API 
Passman offers a [developer API](https://github.com/nextcloud/passman/wiki/API).

## Support Passman
Passman is open source but we’ll gladly accept a beer *or pizza!* Please consider donating:
  * [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6YS8F97PETVU2)
  * [Patreon](https://www.patreon.com/user?u=4833592)
  * [Flattr](https://flattr.com/@passman)
  * bitcoin: 1H2c5tkGX54n48yEtM4Wm4UrAGTW85jQpe

## Code reviews
If you have any code improvements:
  * Clone us
  * Make your edits
  * Add your name to the contributors
  * Send a [PR](https://github.com/nextcloud/passman/pulls)

Or, if you’re feeling lazy, create an issue and we’ll think about it.

## Docker
To run Passman with [Docker](https://www.docker.com/), use our test Docker image. Supply your own self-signed SSL certs or use [Let’s Encrypt](https://letsencrypt.org/). Please note: The Docker image is for _testing *only*_ as database user / password are hardcoded.   
    
If you’d like to *spice up* our Passman Docker image into a full-fledged, production-ready install, you’re welcome to do so. Please note:
  * Port 80 and 443 are used
  * SSL is enabled (or disabled if no certs are found)
  * Container startup time must be less than 15 seconds

Example:   
```
docker run -p 8080:80 -p 8443:443 -v /directory/cert.pem:/data/ssl/cert.pem -v /directory/cert.key:/data/ssl/cert.key brantje/passman
```
        
If you want a production-ready container, use the [Nextcloud Docker](https://hub.docker.com/_/nextcloud/) and install Passman as an app.

## Development
  * Passman uses a single `.js` file for templates which minimizes XHR template requests.   
  * CSS uses SASS, so Ruby and SASS must be installed.
  * `templates.js` and the CSS are built with `grunt`.
  * Watch for changes using `grunt watch`.
  * Run unit tests — Install phpunit globally, setup environment variables in the `launch_phpunit.sh` script, and run the script. All arguments passed to `launch_phpunit.sh` are forwarded to phpunit.

## Main developers
  * Brantje
  * Animalillo

## Contributors
Add yours when creating a [pull request](https://help.github.com/articles/creating-a-pull-request/)!
  * Newhinton
  * [binsky](https://github.com/binsky08)

## FAQ
**Are you adding something to check if malicious code is executing on the browser?**   
No, because malicious code can edit functions that check for malicious code.
