#!/bin/sh

cd $APP_ROOT;

if [ "$INSTALL_DEPS" == "dev" ] || [ "$INSTALL_DEPS" == "DEV" ] ;
then
    npm install
elif [ "$INSTALL_DEPS" == "prod" ] || [ "$INSTALL_DEPS" == "PROD" ] ;
then
    npm install --production
fi

/usr/bin/supervisord -n
