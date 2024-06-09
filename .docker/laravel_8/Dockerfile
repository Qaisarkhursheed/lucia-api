FROM ubuntu:22.04

ENV TZ=Europe/Paris

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# https://tecadmin.net/how-to-install-php-on-ubuntu-22-04/
RUN apt update
RUN apt install -y software-properties-common ca-certificates lsb-release apt-transport-https
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php

RUN apt-get update \
 && apt-get install -y \
    git zip apache2 curl php8.1 php8.1-common php8.1-mysql  \
    php8.1-xml php8.1-xmlrpc php8.1-curl php8.1-gd  \
    php8.1-imagick php8.1-cli php8.1-dev php8.1-imap  \
    php8.1-mbstring php8.1-opcache php8.1-soap php8.1-zip  \
    mysql-client


# International plugin and cron
RUN apt-get install -y \
    php8.1-intl cron



# Networking Tools And Task Management
RUN apt-get install -y \
    iproute2 htop



# COMPOSER AND LARAVEL SUPPORTS
RUN a2enmod rewrite
RUN apt-get -y install wget
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet
RUN mv composer.phar /usr/local/bin/composer



# MS SQL SERVER SUPPORTS
RUN apt-get install -y \
    unixodbc-dev

RUN pecl install sqlsrv
RUN pecl install pdo_sqlsrv
RUN echo "extension=sqlsrv.so" > /etc/php/8.1/mods-available/sqlsrv.ini
RUN echo "extension=pdo_sqlsrv.so" > /etc/php/8.1/mods-available/pdo_sqlsrv.ini

RUN ln -s /etc/php/8.1/mods-available/sqlsrv.ini /etc/php/8.1/cli/conf.d/20-sqlsrv.ini
RUN ln -s /etc/php/8.1/mods-available/pdo_sqlsrv.ini /etc/php/8.1/cli/conf.d/20-pdo_sqlsrv.ini
RUN ln -s /etc/php/8.1/mods-available/sqlsrv.ini /etc/php/8.1/apache2/conf.d/20-sqlsrv.ini
RUN ln -s /etc/php/8.1/mods-available/pdo_sqlsrv.ini /etc/php/8.1/apache2/conf.d/20-pdo_sqlsrv.ini

RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
#RUN curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
RUN curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
RUN apt-get update
RUN ACCEPT_EULA=Y apt-get -y install msodbcsql17
RUN ACCEPT_EULA=Y apt-get -y install mssql-tools


# SUPERVISOR
RUN apt-get install -y \
    supervisor


# REDIS SUPPORT
RUN apt-get install -y \
    php-redis


COPY apache2.conf /etc/apache2/apache2.conf
COPY cacert.pem /etc/php/8.1/cacert.pem
COPY php-apache2/php.ini /etc/php/8.1/apache2/php.ini
COPY php-cli/php.ini /etc/php/8.1/cli/php.ini



WORKDIR /var/www
RUN composer create-project --prefer-dist laravel/laravel:^8.0 sample

EXPOSE 80


# Launch Apache
CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"]
