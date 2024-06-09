#!/bin/bash
git config --global --add safe.directory /var/www/lucia-api
echo "called git config --global --add safe.directory /var/www/lucia-api INSIDE"

git pull

cd .docker/deploy

sh down.sh;
sh rebuild.sh;

cd ../../

printf "We are through!\n"

