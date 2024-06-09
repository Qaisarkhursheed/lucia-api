#!/bin/bash

# FIX PERMISSIONS
usermod -a -G root www-data
chown -R www-data:root storage/logs
chmod -R 777 /var/www/html/storage

# do this if we are in development environment
if [ "$NODE_ENV" = "development" ]; then

  printf "We are in development environment!\n";

else
  # if you run this part in development, you will be creating a redundant link
  # since you can access them directly on development
  # production environment
  printf "We are in production environment!\n";

  touch /home/environs/.env
  ln -s /home/environs/.env /var/www/html/.env
  chmod -R 777 /home/environs

  composer install

  # Run migrations
  php artisan migrate:database

  npm install --save mjml

fi


# START SUPERVISOR
service supervisor restart
service supervisor start


# START CRON
cron

# CHECK IF SSL CONFIGURATION IS REQUIRED
if [ "$USE_SSL" = "true" ]; then

    printf "Configuring SSL\n";

    # CONFIGURE SELF-SIGNED CERTIFICATE
    mkdir /etc/apache2/ssl
    cp /var/www/html/.docker/setup/certificates/self-signed.crt /etc/apache2/ssl/self-signed.crt
    cp /var/www/html/.docker/setup/certificates/self-signed.key /etc/apache2/ssl/self-signed.key
    cp /var/www/html/.docker/setup/certificates/dhparam.pem /etc/apache2/ssl/dhparam.pem
    cp /var/www/html/.docker/setup/certificates/ssl-params.conf /etc/apache2/conf-available/ssl-params.conf

    cp -f /var/www/html/.docker/setup/certificates/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

    a2enmod ssl
    a2enmod headers
    a2ensite default-ssl
    a2enconf ssl-params

fi

# start the real app to keep it running
/usr/sbin/apache2ctl -DFOREGROUND
