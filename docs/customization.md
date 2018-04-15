# Customization

All application customization-related configuration can be found in `config/app.php`.

## Application Name

See the `name` key in `config/app.php` and `APP_NAME` variable in `.env`.

## Application Locale

The application is available in English and Czech.

See `available_locales`, `locale` and `fallback_locale` keys in `config/app.php`.

Translation files are located in `resources/lang`, just the way it is common in any other Laravel application.

## Currencies

You are able to configure which currencies are available in the offer creation form and which currency will be selected by default. See `available_currencies` and `currency` keys in `config/app.php`.

## File upload limits

You are able to limit amount of files per POST request and maximal allowed file size.

See `max_file_uploads` and `upload_max_filesize` keys in `config/app.php`.

