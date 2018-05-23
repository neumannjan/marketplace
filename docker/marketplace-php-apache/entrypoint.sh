#!/bin/sh
printenv | grep -E "^APP_ROOT" | sed 's/^\(.*\)$/export \1/g' > /get_app_root.sh

cd $APP_ROOT;

if [ "$INSTALL_DEPS" == "dev" ] || [ "$INSTALL_DEPS" == "DEV" ] ;
then
    composer install
elif [ "$INSTALL_DEPS" == "prod" ] || [ "$INSTALL_DEPS" == "PROD" ] ;
then
    composer install --no-dev
fi

if [ "$MIGRATE_DB" == "true" ] || [ "$MIGRATE_DB" == "TRUE" ] || [ "$MIGRATE_DB" == "1" ] ;
then
    php /app/artisan migrate
fi

/usr/bin/supervisord -n
