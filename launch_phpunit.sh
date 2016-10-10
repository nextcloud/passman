#!/bin/bash
# Setup your testing environment paths
export NEXTCLOUD_BASE_DIR=/var/www/html/nextcloud/
export NEXTCLOUD_CONFIG_DIR=/var/www/html/nextcloud/config/
export NEXTCLOUD_CONFIG_FILE=config.php

# Lanuch the actual tests
phpunit $@
