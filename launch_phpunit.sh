#!/bin/bash
# Setup your testing environment paths
export SERVER_BASE_DIR=/var/www/html/ncdev/
export SERVER_CONFIG_DIR=/var/www/html/ncdev/config/
export SERVER_CONFIG_FILE=config.php

# Lanuch the actual tests
phpunit -v $@
