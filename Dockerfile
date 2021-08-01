# Nextcloud - passman - custom dev container

FROM ubuntu:20.04
RUN /bin/bash -c "export DEBIAN_FRONTEND=noninteractive" && \
        /bin/bash -c "debconf-set-selections <<< 'mariadb-server mysql-server/root_password password PASS'" && \
        /bin/bash -c "debconf-set-selections <<< 'mariadb-server mysql-server/root_password_again password PASS'" && \
        /bin/bash -c "debconf-set-selections <<< 'tzdata    tzdata/Zones/Europe select  Madrid'" && \
    	/bin/bash -c "echo \"Europe/Zurich\" > /etc/timezone " && \
    	/bin/bash -c "ln -fs /usr/share/zoneinfo/`cat /etc/timezone` /etc/localtime" && \
        apt-get -y update && apt-get install -y \
        apache2 \
        cowsay \
        cowsay-off \
        git \
        curl \
        libapache2-mod-php7.4 \
        mariadb-server \
        php7.4 \
        php7.4-mysql \
        php7.4-curl \
        php-dompdf \
        php7.4-gd \
        php7.4-mbstring \
        php7.4-xml \
        php7.4-zip \
        php7.4-intl \
        php7.4-bcmath \
        php7.4-gmp \
        php7.4-imagick \
        phpunit \
        wget \
        openssh-server \
        npm \
        ruby-dev \
        composer \
        sudo

RUN  gem install sass && \
     a2enmod ssl && \
     ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled && \
     git clone https://github.com/nextcloud/passman /var/www/passman && \
     cd /var/www/passman && npm install && \
     npm install -g grunt-cli

ADD https://raw.githubusercontent.com/nextcloud/travis_ci/master/before_install.sh /var/www/passman

RUN service mysql restart && \
    mysql -uroot -pPASS -e "SET PASSWORD = PASSWORD('');" && \
    sed  -i '0,/.*SSLCertificateChainFile.*/s/.*SSLCertificateChainFile.*/SSLCertificateChainFile \/etc\/ssl\/private\/fullchain.pem/' /etc/apache2/sites-enabled/default-ssl.conf && \
    sed -i '0,/.*ssl-cert-snakeoil.pem.*/s/.*ssl-cert-snakeoil.pem.*/SSLCertificateFile \/etc\/ssl\/private\/cert.pem/' /etc/apache2/sites-enabled/default-ssl.conf && \
    sed -i '0,/.*SSLCertificateKeyFile.*/s/.*SSLCertificateKeyFile.*/SSLCertificateKeyFile \/etc\/ssl\/private\/privkey.pem/' /etc/apache2/sites-enabled/default-ssl.conf && \
    echo "echo hhvm" > /bin/phpenv && chmod +x /bin/phpenv && \
    cd /var/www/passman && \
    chmod +x before_install.sh && \
    sleep 1 && \
    /bin/bash -c "./before_install.sh passman stable21 mysql; exit 0" && \
    rm /var/www/server/apps/passman/before_install.sh && \
    mv /var/www/server/* /var/www/html/ && \
    cd /var/www/html/ && \
    chmod +x occ && \
    service mysql restart && \
    ./occ maintenance:install --database-name oc_autotest --database-user oc_autotest --admin-user admin --admin-pass admin --database mysql --database-pass 'owncloud' && \
    sed -i 's/\/var\/www\/server/\/var\/www\/html/g' /var/www/html/config/config.php && \
    cat /var/www/html/config/config.php && \
    ./occ check && \
    ./occ status && \
    ./occ app:list && \
    ./occ app:enable passman && \
    ./occ upgrade && \
    ./occ config:system:set defaultapp --value=passman && \
    ./occ config:system:set appstoreenabled --value=false && \
    ./occ config:system:set trusted_domains 2 --value=172.17.0.2 && \
    ./occ config:system:set trusted_domains 3 --value=passman.cc && \
    ./occ config:system:set trusted_domains 4 --value=demo.passman.cc && \
                chown -R www-data /var/www
EXPOSE 80
EXPOSE 443

COPY entrypoint.sh /
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD []

#/usr/games/cowsay -f dragon.cow "you might now login using username:admin password:admin" && \ 
