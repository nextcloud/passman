#!/bin/bash

# SIGTERM-handler
term_handler() {
  service apache2 stop
  service mysql stop
  exit 0
}

set -x

service ssh start
service mysql start
service apache2 start


trap 'kill ${!}; term_handler' SIGTERM

/usr/games/cowsay -f dragon.cow "you might now login using username:admin password:admin"

# wait forever
while true
do
    tail -f /var/www/html/data/nextcloud.log & wait ${!}
done
