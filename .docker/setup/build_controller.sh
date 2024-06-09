#!/bin/bash

if [ "$NODE_ENV" = "development" ]; then

  printf "We are in development environment!\n";

else

  printf "We are in production environment!\n";

  # try to remove the original packed version
  rm -f /var/www/html/.env
  rm -rf /var/www/html/vendor

fi


printf "We are done building the image script.\n";
