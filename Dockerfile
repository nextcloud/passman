# Nextcloud - passman - custom dev container

FROM ubuntu:22.04

RUN /bin/bash -c "export DEBIAN_FRONTEND=noninteractive" && \
        /bin/bash -c "debconf-set-selections <<< 'tzdata    tzdata/Zones/Europe select  Madrid'" && \
        /bin/bash -c "echo \"Europe/Zurich\" > /etc/timezone " && \
        /bin/bash -c "ln -fs /usr/share/zoneinfo/`cat /etc/timezone` /etc/localtime" && \
        apt-get -y update && apt-get install -y \
        npm \
        ruby-dev

RUN /bin/bash -c "export DEBIAN_FRONTEND=noninteractive" && \
        /bin/bash -c "debconf-set-selections <<< 'mariadb-server mysql-server/root_password password PASS'" && \
        /bin/bash -c "debconf-set-selections <<< 'mariadb-server mysql-server/root_password_again password PASS'" && \
        apt-get install -y \
        apache2 \
        cowsay \
        cowsay-off \
        git \
        curl \
        mariadb-server \
        software-properties-common \
        wget \
        openssh-server \
        sudo

RUN /bin/bash -c "export DEBIAN_FRONTEND=noninteractive" && add-apt-repository -y ppa:ondrej/php

RUN /bin/bash -c "export DEBIAN_FRONTEND=noninteractive" && \
        apt-get install -y \
        libapache2-mod-php8.1 \
        php8.1 \
        php8.1-mysql \
        php8.1-curl \
        php-dompdf \
        php8.1-gd \
        php8.1-mbstring \
        php8.1-xml \
        php8.1-zip \
        php8.1-intl \
        php8.1-bcmath \
        php8.1-gmp \
        php8.1-imagick

RUN /bin/bash -c "export DEBIAN_FRONTEND=noninteractive" && \
        wget -O /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-9.phar && \
        wget -O /usr/local/bin/composer https://getcomposer.org/download/latest-stable/composer.phar && \
        chmod +x /usr/local/bin/phpunit /usr/local/bin/composer

RUN gem install sass
RUN a2enmod ssl
RUN ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled
RUN git clone https://github.com/nextcloud/passman /var/www/passman
RUN cd /var/www/passman && npm install && npm install -g grunt-cli
ADD https://raw.githubusercontent.com/nextcloud/travis_ci/master/before_install.sh /var/www/passman

RUN service mariadb restart && \
    mysql -uroot -pPASS -e "SET PASSWORD = PASSWORD('');" && \
    sed  -i '0,/.*SSLCertificateChainFile.*/s/.*SSLCertificateChainFile.*/SSLCertificateChainFile \/etc\/ssl\/private\/fullchain.pem/' /etc/apache2/sites-enabled/default-ssl.conf && \
    sed -i '0,/.*ssl-cert-snakeoil.pem.*/s/.*ssl-cert-snakeoil.pem.*/SSLCertificateFile \/etc\/ssl\/private\/cert.pem/' /etc/apache2/sites-enabled/default-ssl.conf && \
    sed -i '0,/.*SSLCertificateKeyFile.*/s/.*SSLCertificateKeyFile.*/SSLCertificateKeyFile \/etc\/ssl\/private\/privkey.pem/' /etc/apache2/sites-enabled/default-ssl.conf && \
    echo "echo hhvm" > /bin/phpenv && chmod +x /bin/phpenv && \
    cd /var/www/passman && \
    chmod +x before_install.sh && \
    sleep 1 && \
    /bin/bash -c "./before_install.sh passman stable26 mysql; exit 0" && \
    rm /var/www/server/apps/passman/before_install.sh && \
    mv /var/www/server/* /var/www/html/ && \
    cd /var/www/html/ && \
    chmod +x occ && \
    service mariadb restart && \
    sed -i 's/\/var\/www\/server/\/var\/www\/html/g' /var/www/html/config/config.php && \
    ./occ check && \
    ./occ status && \
    ./occ app:list && \
    ./occ upgrade && \
    ./occ config:system:set defaultapp --value=passman && \
    ./occ config:system:set appstoreenabled --value=false && \
    ./occ config:system:set log_type --value=file && \
    ./occ config:system:set loglevel --value=1 && \
    ./occ config:system:set trusted_domains 2 --value=172.17.0.2 && \
    ./occ config:system:set trusted_domains 3 --value=passman.cc && \
    ./occ config:system:set trusted_domains 4 --value=demo.passman.cc && \
    ./occ config:system:set trusted_domains 5 --value=10.0.2.2 && \
                chown -R www-data /var/www
EXPOSE 80
EXPOSE 443

COPY entrypoint.sh /
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD []

#/usr/games/cowsay -f dragon.cow "you might now login using username:admin password:admin" && \
