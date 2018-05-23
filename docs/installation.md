# Installation

## Requirements

### Backend Requirements

* _PHP &gt;= 7.1.3_
  * _Composer_
  * _Intl Extension_ (for price and currency formatting)
  * _SQlite3 Extension_ (requirement of the search implementation)
  * _OpenSSL Extension_
  * _PDO Extension_
  * _Mbstring Extension_
  * _Tokenizer Extension_
  * _XML Extension_
  * _Ctype Extension_
  * _JSON Extension_
* _Redis 3+_
* _MySQL &gt;= 5.7_ (other DB platforms are unlikely to work)
* _Local storage_ for image uploads
  * Other filesystem support (such as S3) is not implemented.
* _Any e-mail service_ supported by Laravel, or a _SMTP server_.

### WebSocket Server Requirements

* _Node.js_ (preferably at least current LTS)
  * _npm_
* _Redis 3+_

## Quick Installation Using Docker

A [Docker](https://www.docker.com/) `docker-compose.yml` file is available to quickly provide all requirements and serve the application with only a few additional steps.

It handles the following:

* Installation and configuration of Apache, PHP, Redis, MySQL and Node.js
* Launch of the HTTP server
* Launch of the WebSocket server 
* Launch of Laravel's [queue worker](https://laravel.com/docs/5.5/queues)
* Scheduling Laravel's `schedule:run` command to run every minute using cron.

!> **Provided Docker setup is meant for local use only**. In order to modify it for production, please see `docker-compose.yml` and the `docker` directory, which contains custom Dockerfiles.

### First Use

Docker and `docker-compose` (newest version - recommended installation using `pip`) are required.

The first step is to prepare the `.env` file:

```bash
$ cp .env.example .env
```

Next, run the `docker-compose` command (replace `prod` with `dev` to install development dependencies as well):

```bash
$ INSTALL_DEPS=prod MIGRATE_DB=true docker-compose up
```

Wait for all operations to finish. The log should stop writing new messages at a certain point.
All dependencies will then have been installed and the server will have been launched.

Now, user with administrator privileges has to be created.
To learn how to do that, see [Database Access Caveat](#database-access-caveat) and
[admin user creation](#create-an-admin-user).

The application will then be available on this URL:
```
http://localhost:8080/
```

To shut it down, execute the following:
```bash
$ docker-compose down
```

### Database Access Caveat

All `php artisan` commands that access the database have to be executed in the `web` Docker container. It can be done like this:

```bash
$ docker-compose exec web php app/artisan <rest of the command>
```

Alternatively, the following may be done:

```bash
$ docker-compose exec web bash
$ cd app
```

After executing these commands you will be in the docker `web` container and will be able to run all `php artisan` commands normally.

You may then leave the container:

```bash
$ exit
```

### Usage

Launch the server with the following command:
```bash
$ docker-compose up
```

The application will then be available on this URL:
```
http://localhost:8080/
```

To shut it down, execute the following:
```bash
$ docker-compose down
```

## Full Installation Instructions

?> Good knowledge of [Laravel](https://laravel.com/docs/5.6) is an advantage for those installing and maintaining the application.
  Instructions below contain, however, all necessary installation information.

`cd` to the project directory.

### Ensure Correct Directory Permissions

Read [Laravel's documentation](https://laravel.com/docs/5.6/installation#configuration) for more information about directory permissions.

### Configure Your HTTP Server (Apache, Nginx, ...)

Your web server's document / web root should be the `public` directory.

### Install Composer Production Dependencies

```bash
$ composer install --no-dev
```

### Install NPM Dependencies

```bash
$ npm install --production
```

Skip the `--production` flag if you intend to [build assets](development.md#building-assets).

### Prepare the `.env` File

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

### Set Additional `.env` Variables

Set the `APP_URL` variable appropriately.

### Configure Laravel's Queue Worker and Task Scheduling

Read [Laravel's documentation](https://laravel.com/docs/5.6/scheduling#introduction) for more information about task scheduling.

Ensure that the following service is running: (multiple such processes may be running in parallel)

```bash
$ php artisan queue:work --sleep=3 --tries=3
```

!> Queue worker is required for search indexing and image resizing. 
    If you do not want to use a queue (_not recommended_), change the `SCOUT_QUEUE` environment variable to `false` and the `QUEUE_DRIVER` environment variable to `sync`.

### Configure MySQL and Redis Connections

Configuration may be done in `.env` or `config/database.php`. Details are in [Laravel's documentation](https://laravel.com/docs/5.6).

No database solutions other than MySQL &gt;= 5.7 are supported. MySQL-specific database queries are run by the `scopeConversationsWith` function of `app/Message.php`.

### Configure E-Mail Sending

!> This is not preconfigured by the provided Docker configuration! No e-mails are sent by default, they are just logged to `storage/logs/laravel.log`.

See [Laravel's documentation](https://laravel.com/docs/5.6/mail#introduction).

### Generate a Unique App Key

```bash
$ php artisan key:generate
```

### Run Database Migrations

```bash
$ php artisan migrate
```

### Create an Admin User

```bash
$ php artisan user:create <username> <email> <password> -a
```

The `-a` option gives the user admin privileges.

?> If you execute this command incorrectly, e.g., with a typo in e-mail, password or username, you will have to modify the database by hand (`users` table).
  Alternatively, you may remove all data from the whole database and re-migrate by executing `php artisan migrate:fresh`.

### Fix Newly Created File Permissions

By creating a new user, the `storage/search/users.index` file has been created. Ensure that it is readable and writable by the web server.

### Configure and Launch the WebSocket Server

First, configure the `WEBSOCKET_PORT` variable. It determines which port the WebSocket server will run on. `.env` is the only place where this value has to be set (unless you are using provided `docker-compose.yml`).

After that, make sure that the `WEBSOCKET_APP_ENDPOINT` variable matches the `APP_ENV` variable. You may also remove this variable entirely.

Then, run the service:

```bash
$ node websocket
```

[laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server) is used for this. See the bottom of the `websocket` file (in root project directory) to see how it is configured (it pulls information from the `.env` file automatically on its launch).
