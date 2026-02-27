#!/bin/bash

# SIGTERM-handler
term_handler() {
  service apache2 stop
  service mariadb stop
  exit 0
}

set -x

service ssh start
service mariadb start
service apache2 start

# Start file watch for browser hot reload inside the devcontainer
if [ "$DEV" = "1" ]; then
  /auto-reload.sh &
fi


trap 'kill ${!}; term_handler' SIGTERM

/usr/games/cowsay -f dragon.cow "you might now login using username:admin password:admin"

# wait forever
while true
do
    tail -f /var/www/html/data/nextcloud.log & wait ${!}
done
