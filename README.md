# Passman
Passman is a full featured, open source password manager for Nextcloud.

[![PHPUnit SQLite](https://github.com/nextcloud/passman/actions/workflows/phpunit-sqlite.yml/badge.svg)](https://github.com/nextcloud/passman/actions/workflows/phpunit-sqlite.yml)

## Join us!
Visit the [“Passman General Talk” Telegram Group](https://t.me/passman_general) to participate in all sorts of topical discussions about Passman and its apps!

## Contents
  * [Features](#features)
  * [External apps](#external-apps)
  * [Screenshots](#screenshots)
  * [Database Compatibility](#database-compatibility)
  * [Security](#security)
    * [Password generation](#password-generation)
    * [Storing credentials](#storing-credentials)
  * [API](#api)
  * [Docker](#docker)
  * [Development](#development)
  * [Support Passman](#support-passman)
  * [Contributing](#contributing)

## Features
* Multiple vaults
* Vault keys are never sent to the server
* 256-bit AES-encrypted credentials (see [security](#security))
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

**Try a Passman demo [here](https://demo.passman.cc).**

## External apps
### Android app
Our native [Passman Android](https://github.com/nextcloud/passman-android) app is available for download from the [Google Play Store](https://play.google.com/store/apps/details?id=es.wolfi.app.passman.alpha), [IzzyOnDroid](https://apt.izzysoft.de/fdroid/index/apk/es.wolfi.app.passman) and [F-Droid](https://f-droid.org/app/es.wolfi.app.passman).

### Browser extension
[The legacy Firefox / Chrome extension](https://github.com/nextcloud/passman-webextension) is the "old-stable", but **not maintained** and no longer available in the Chrome Web Store since it's MV2 based.

A [follow-up extension](https://gitlab.com/binsky08/passman-webextension-v3) is in active development and **currently considered unstable**. It's available but in open beta / development phase.

## Screenshots
![Logged in to vault](http://i.imgur.com/ciShQZg.png)

![Credential selected](http://i.imgur.com/3tENldT.png)

![Edit credential](http://i.imgur.com/Iwm3hUe.png)

![Password tool](http://i.imgur.com/ZYkN70r.png)

More screenshots are available on the [Nextcloud App Store](https://apps.nextcloud.com/apps/passman) and [imgur](http://imgur.com/a/giKVt).

## Database Compatibility

|                 | Supported |
|:----------------|:---------:|
| SQLite          |     •     |
| MySQL / MariaDB |     •     |
| PostgreSQL      |     •     |

CI runs PHPUnit against SQLite on GitHub Actions.

## Security

### Password generation
Passman can generate passwords *and* measure their strength using [zxcvbn](https://github.com/dropbox/zxcvbn).

![](https://i.imgur.com/2qVBUfM.png)

Generate passwords as you like.

![](https://i.imgur.com/jcRicOV.png)

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
Unfortunately it is very outdated and not maintained. You're welcome to update it.

## Docker
Passman Docker images are currently maintained in [passman-dev-docker-build](https://github.com/binsky08/passman-dev-docker-build).

| Image | Docker Hub | Use for |
| :--- | :--- | :--- |
| **Development** | [binsky/passman-dev](https://hub.docker.com/r/binsky/passman-dev) | Local hacking: bind-mount your checkout, run grunt, try different Nextcloud/PHP stacks |
| **Demo** | [binsky/passman-demo](https://hub.docker.com/r/binsky/passman-demo) | Pre-baked instances (e.g. [demo.passman.cc](https://demo.passman.cc)) without dev tooling |

Default login for all images: `admin` / `admin`.

Quick start (development):
```
docker run -d -p 8080:80 -p 8443:443 \
  -v /path/to/passman:/var/www/html/apps/passman \
  --name passman-dev \
  binsky/passman-dev:latest
```

See the [repository README](https://github.com/binsky08/passman-dev-docker-build#quick-start-development) for TLS setup, available tags, and SSH/sshfs mounting.

For production deployments, use the official [Nextcloud Docker](https://hub.docker.com/_/nextcloud/) image and install Passman as an app.

## Development
Start from a [passman-dev](https://github.com/binsky08/passman-dev-docker-build) container, then work inside `/var/www/html/apps/passman`:

  * Passman uses a single `.js` file for templates which minimizes XHR template requests.
  * Our CSS is written in SASS.
  * `templates.js` and the CSS are built with `grunt` / `grunt build`.
  * Watch for changes using `grunt watch`.
  * To run PHP unit tests in the running dev container, ...
    * run on your host: `make test` (full suite) or `make testNoDb` (without DB group). Generate a Clover coverage report with `make test-coverage` (requires pcov or xdebug in the container). Customize the container name with `DOCKER_CONTAINER=passman-dev-nc34-85-testing make test`.
    * or run in the container: `cd /var/www/html/apps/passman && composer run test`
    * after switching branches or on cache-issues, run `cd /var/www/html/apps/passman && composer run test:clear-cache`

## Support Passman
Passman is open source and lives from contributions like [pull request](https://github.com/nextcloud/passman/pulls),
but we’ll also gladly accept a Club Mate *or pizza!*

Please consider donating:
* [Patreon](https://www.patreon.com/passman)
* [Ko-Fi](https://ko-fi.com/passman)

## Contributing
Pull requests and [issues](https://github.com/nextcloud/passman/issues) are welcome. Fork the repo, make your changes, and open a [pull request](https://github.com/nextcloud/passman/pulls). Add your name to the contributors list below when you do.

**Maintainers:**
- [Brantje](https://github.com/brantje)
- [Animalillo](https://github.com/animalillo)
- [binsky](https://github.com/binsky08)

**Contributors:**
- Newhinton
- [HolgerHees](https://github.com/HolgerHees)

## FAQ
**Are you adding something to check if malicious code is executing on the browser?**
No, because malicious code can edit functions that check for malicious code.
