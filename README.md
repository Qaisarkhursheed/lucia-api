# LUCIA API

This api is based on Lumen framework version 8.


## Installation using docker
- Install docker. [Click this link for documentation.](https://docs.docker.com/engine/install/ubuntu/)
- Install docker-compose `[ sudo apt get docker-compose ]`
- Create Laravel 8 Image `[ cd .docker/laravel_8/ && sh build.sh ]`
- Run the app `[ cd .docker/dev/ && sh up.sh ]`



## Installation using apache

#### Requirements
- PHP >= 7.3
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension

Just place the folder in the web root and point the
domain to the root folder. The **.htaccess** file will handle the request.
<br/> Do not forget to set the **.env** file with required parameters
<br/> See the **.env.example** file for how to fill/create the **.env** file properly.

- Run command
- `composer install`
- `php artisan migrate:database`
