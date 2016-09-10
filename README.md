# passman
Passman is a password manager for Nextcloud
Place this app in **nextcloud/apps/**

## Building the app

The app can be built by using the provided Makefile by running:
`npm install`

## Building the templates.
Passman uses compiled Angular js templates.   
A grunt task compiles the views (located in templates/views), to `templates.js`. 
You can compile the templates using `grunt`
If you are a developer and want to watch the changes you made use: `grunt watch`
