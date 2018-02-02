FROM node:9.5-stretch

RUN apt-get update && apt-get install -y supervisor

COPY laravel-echo-server.conf /etc/supervisor/conf.d

ENTRYPOINT /bin/bash -c "/usr/bin/supervisord -n"