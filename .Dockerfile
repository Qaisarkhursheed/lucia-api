FROM laravel:8

# What is this package for
#RUN apt-get install php-http php-propro php-raphf -y
RUN apt-get update
RUN apt-get install php-http php-raphf -y

COPY . /var/www/html

# INSTALL NODE
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt -y remove nodejs nodejs-doc
RUN apt -y install nodejs


# SCHEDULING
# -------------------------------------
# Add cronjob, you can use below if you want log
# RUN (crontab -l 2>/dev/null; echo "* * * * * php /var/www/html/artisan schedule:run >> /var/www/html/storage/logs/crontab.log") | crontab -
RUN (crontab -l 2>/dev/null; echo "* * * * * php /var/www/html/artisan schedule:run >> /dev/null 2>&1") | crontab -


# TO Supervisor
# You can add as many conf you have here
COPY .docker/setup/lumen-queue-worker.conf /etc/supervisor/conf.d/lumen-queue-worker.conf


WORKDIR /var/www/html

ARG NODE_ENV
ARG USE_SSL

RUN bash .docker/setup/build_controller.sh

# You can comment out if you don't want SSL
EXPOSE 443


CMD [ "bash", ".docker/setup/start_up.sh" ]
