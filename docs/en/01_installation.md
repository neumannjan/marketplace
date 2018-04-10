# Installation

## Requirements

### Backend requirements

* _PHP &gt;= 7.1.3_
  * _Composer_
  * _Intl Extension_ \(for currency formatting\)
  * _SQlite3 Extension_ \(requirement of our search implementation\)
  * _OpenSSL Extension_
  * _PDO Extension_
  * _Mbstring Extension_
  * _Tokenizer Extension_
  * _XML Extension_
  * _Ctype Extension_
  * _JSON Extension_
* _Redis 3+_
* _MySQL &gt;= 5.7_ \(other DB platforms are unlikely to work\)
* _Local storage_ for image uploads
  * Other filesystem support \(such as S3\) is not implemented
* _Any e-mail service_ supported by Laravel, or a _SMTP server_

### WebSocket server requirements

* _Node.js_ \(preferably at least current LTS\)
  * _npm_
* _Redis 3+_

## Docker

A [Docker](https://www.docker.com/) `docker-compose.yml` file is available to quickly provide all requirements and serve the application with only a few additional steps.

It handles the following:

* Installation and configuration of Apache, PHP, Redis, MySQL and Node.js
* Launch of the HTTP server
* Launch of the WebSocket server 
* Launch of Laravel's queue worker
* Scheduling Laravel's `schedule:run` command to run every minute using cron.

**It is meant to be used for local development only**. If you want to modify it for production, see `docker-compose.yml` and the `docker` directory, which contains custom Dockerfiles.

### Usage

Docker and `docker-compose` are required. Everything is then launched with a single command:

```bash
$ docker-compose up
```

To shut it down, execute the following:

```bash
$ docker-compose down
```

### Database access caveat

All `php artisan` commands that access the database have to be run from the `web` Docker container! Such commands have to be executed like below:

```bash
 $ docker exec -it marketplace_web_1 php app/artisan <rest of the command>
```

> `marketplace_web_1` may be different depending on the name of the Docker container that had been created by docker-compose.

Alternatively, you may do the following:

```bash
$ docker exec -it marketplace_web_1 bash

$ cd app
```

Now you are in the docker `web` container and can run all `php artisan` commands regularly.

You may then leave the container:

```bash
$ exit
```

## Installation

`cd` to the project directory.

### Ensure correct directory permissions

See [Laravel's documentation](https://laravel.com/docs/5.6/installation#configuration) on directory permissions.

### Configure your HTTP server \(Apache, Nginx,...\)

> If you are using provided `docker-compose.yml`, skip this step.

Your web server's document / web root should be the `public` directory.

### Install Composer production dependencies

```bash
$ composer install --no-dev
```

### Install npm dependencies

```bash
$ npm install --production
```

Skip the `--production` flag if you intend to [build assets](01_installation.md#building-assets).

### Prepare the `.env` file

Rename `.env.example` to `.env`.

```bash
$ cp .env.example .env
```

Variables `APP_ENV` and `APP_DEBUG` are preconfigured for development. For production, set them to `production` and `false`, respectively, like below:

```text
APP_ENV=production
APP_DEBUG=false
```

`APP_NAME` may be set to any value you desire.

### Set additional `.env` variables

> If you are using provided `docker-compose.yml`, skip this step.

Set the `APP_URL` variable appropriately.

### Configure Laravel's queue worker and task scheduling

> If you are using provided `docker-compose.yml`, skip this step.

See [Laravel's documentation](https://laravel.com/docs/5.6/scheduling#introduction) for task scheduling and ensure that the following service is running: \(multiple processes may be running in parallel\)

```bash
$ php artisan queue:work --sleep=3 --tries=3
```

### Configure MySQL and Redis connections

> If you are using provided `docker-compose.yml`, skip this step.

See `config/database.php` and `.env` files. Details are in [Laravel's documentation](https://laravel.com/docs/5.6).

Remember that no database solutions other than MySQL &gt;= 5.7 are supported! MySQL-specific database queries are run by the `scopeConversationsWith` function of `app/Message.php`.

### Configure e-mail sending

This is not preconfigured by `docker-compose.yml`! No e-mails are sent by default, only logged to `storage/logs/laravel.log`.

See [Laravel's documentation](https://laravel.com/docs/5.6/mail#introduction).

### Generate a unique app key

```bash
$ php artisan key:generate
```

### Run database migrations

```bash
$ php artisan migrate
```

> **Warning:** If you are using provided `docker-compose.yml`, the [database access caveat](01_installation.md#database-access-caveat) applies here!

### Create an admin user

```bash
$ php artisan user:create <username> <email> <password> -a
```

The `-a` option gives the user admin privileges.

> **Warning:** If you are using provided `docker-compose.yml`, the [database access caveat](01_installation.md#database-access-caveat) applies here!
>
> If you execute this command incorrectly, e.g., with a typo in e-mail, password or username, you will have to modify the database by hand \(`users` table\). Alternatively, you may remove all data from the whole database and re-migrate by executing `php artisan migrate:fresh`.

### Fix newly created file permissions

> If you are using provided `docker-compose.yml`, skip this step.

By creating a new user, the `storage/search/users.index` file has been created. Ensure that it is readable and writable by the web server.

### Launch and configure the websocket server

> If you are using provided `docker-compose.yml`, skip this step.

First, configure the `WEBSOCKET_PORT` variable. It determines which port the websocket server will run on. This is the only place where this value has to be set \(unless you are using provided `docker-compose.yml`\).

After that, make sure that the `WEBSOCKET_APP_ENDPOINT` variable matches the `APP_ENV` variable. You may also remove this variable entirely.

Then, run the service:

```bash
$ node websocket
```

[laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server) is used for this. See the bottom of the `websocket` file \(in root project directory\) to see how it is configured \(it pulls information from the `.env` file automatically on its launch\).

## Building assets

[Webpack 4](https://webpack.js.org/) is used here. These are the provided commands:

```bash
# Building for development:
$ npm run dev

# Building for development and watching for file changes:
$ npm run watch

# Building for production:
$ npm run prod
```

## Running tests

[Codeception](https://codeception.com/) is used here. However, the vast majority of the application is uncovered by tests due to time pressure.

All tests are meant to be run with our Docker configuration. The application should, however, be launched differently:

```bash
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml up
```

This launches a Selenium driver alongside the application.

To shut everything down, execute the following:

```bash
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml down
```

