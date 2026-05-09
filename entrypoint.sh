#!/bin/bash

# SIGTERM-handler
term_handler() {
    service apache2 stop

    if [ -f /etc/init.d/mariadb ]; then
        service mariadb stop
    else
        service mysql stop
    fi

    exit 0
}

set -x

service ssh start

if [ -f /etc/init.d/mariadb ]; then
    service mariadb start
else
    service mysql start
fi

service apache2 start

sudo -u www-data php /var/www/html/occ app:disable passman
sudo -u www-data php /var/www/html/occ app:enable passman

trap 'kill ${!}; term_handler' SIGTERM

# wait forever
while true
do
    if [ -f /var/www/html/data/nextcloud.log ]; then
        tail -f /var/www/html/data/nextcloud.log & wait ${!}
    fi

    if [ -f /var/www/html/data-autotest/nextcloud.log ]; then
        tail -f /var/www/html/data-autotest/nextcloud.log & wait ${!}
    fi
done
