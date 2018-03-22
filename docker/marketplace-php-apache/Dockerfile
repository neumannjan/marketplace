FROM webdevops/php-apache-dev:ubuntu-16.04

RUN apt-get install -y supervisor

COPY laravel-worker.conf /opt/docker/etc/supervisor.d

COPY crontab /opt/docker/etc/cron/laravel
RUN chmod 0644 /opt/docker/etc/cron/laravel

COPY env_to_file.sh /entrypoint.d