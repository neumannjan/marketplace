FROM webdevops/php-apache-dev:ubuntu-16.04

RUN apt-get install supervisor

COPY laravel-worker.conf /opt/docker/etc/supervisor.d
