# Installation

## Requirements

### Backend requirements
* *PHP >= 7.1.3*
    * *Composer*
    * *Intl Extension* (for currency formatting)
    * *SQlite3 Extension* (requirement of our search implementation)
    * *OpenSSL Extension*
    * *PDO Extension*
    * *Mbstring Extension*
    * *Tokenizer Extension*
    * *XML Extension*
    * *Ctype Extension*
    * *JSON Extension*
* *Redis 3+*
* *MySQL >= 5.7* (other DB platforms are unlikely to work)
* *Local storage* for image uploads
    * Other filesystem support (such as S3) is not implemented
* *Any e-mail service* supported by Laravel, or a *SMTP server*

### WebSocket server requirements
* *Node.js* (preferably at least current LTS)
* *Redis 3+*

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
```sh
$ docker-compose up
```

To shut it down, execute the following:
```sh
$ docker-compose down
```

### Database access caveat
All `php artisan` commands that access the database have to be run from the `web` Docker container! Such commands have to be executed like below:

```sh
 $ docker exec -it marketplace_web_1 php app/artisan <rest of the command>
```

> `marketplace_web_1` may be different depending on the name of the Docker container that had been created by docker-compose.

Alternatively, you may do the following:
```sh
$ docker exec -it marketplace_web_1 bash

$ cd app
```
Now you are in the docker `web` container and can run all `php artisan` commands regularly.

You may then leave the container:
```sh
$ exit
```

## Installation

`cd` to the project directory.

### Ensure correct directory permissions
See [Laravel's documentation](https://laravel.com/docs/5.6/installation#configuration) on directory permissions.

### Configure your HTTP server (Apache, Nginx,...)
> If you are using provided `docker-compose.yml`, skip this step.

Your web server's document / web root should be the `public` directory. 

### Install Composer production dependencies
```sh
$ composer install --no-dev
```


### Prepare the `.env` file
Rename `.env.example` to `.env`.
```sh
$ cp .env.example .env
```

Variables `APP_ENV` and `APP_DEBUG` are preconfigured for development.
For production, set them to `production` and `false`, respectively, like below:
```
APP_ENV=production
APP_DEBUG=false
```

`APP_NAME` may be set to any value you desire.

### Set additional `.env` variables
> If you are using provided `docker-compose.yml`, skip this step.

Set the `APP_URL` variable appropriately.

### Configure Laravel's queue worker and task scheduling
> If you are using provided `docker-compose.yml`, skip this step.

See [Laravel's documentation](https://laravel.com/docs/5.6/scheduling#introduction) for task scheduling and ensure that the following service is running: (multiple processes may be running in parallel)
```sh
$ php artisan queue:work --sleep=3 --tries=3
```

### Configure MySQL and Redis connections
> If you are using provided `docker-compose.yml`, skip this step.

See `config/database.php` and `.env` files. Details are in [Laravel's documentation](https://laravel.com/docs/5.6).

Remember that no database solutions other than MySQL >= 5.7 are supported! MySQL-specific database queries are run by the `scopeConversationsWith` function of `app/Message.php`.

### Configure e-mail sending
This is not preconfigured by `docker-compose.yml`! No e-mails are sent by default, only logged to `storage/logs/laravel.log`.

See [Laravel's documentation](https://laravel.com/docs/5.6/mail#introduction).

### Generate a unique app key
```sh
$ php artisan key:generate
```

### Run database migrations
```sh
$ php artisan migrate
```

> **Warning:** If you are using provided `docker-compose.yml`, the [database access caveat](#database-access-caveat) applies here!

### Create an admin user
```sh
$ php artisan user:create <username> <email> <password> -a
```
The `-a` option gives the user admin privileges.

> **Warning:** If you are using provided `docker-compose.yml`, the [database access caveat](#database-access-caveat) applies here!

> If you execute this command incorrectly, e.g., with a typo in e-mail, password or username, you will have to modify the database by hand (`users` table). Alternatively, you may remove all data from the whole database and re-migrate by executing `php artisan migrate:fresh`.

### Fix newly created file permissions
> If you are using provided `docker-compose.yml`, skip this step.

By creating a new user, the `storage/search/users.index` file has been created. Ensure that it is readable and writable by the web server.

### Launch and configure the websocket server
> If you are using provided `docker-compose.yml`, skip this step.

First, configure the `WEBSOCKET_PORT` variable. It determines which port the websocket server will run on. This is the only place where this value has to be set (unless you are using provided `docker-compose.yml`).

After that, make sure that the `WEBSOCKET_APP_ENDPOINT` variable matches the `APP_ENV` variable. You may also remove this variable entirely.

Then, run the service:
```sh
$ node websocket
```

We are using [laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server). See the bottom of the `websocket` file (in root project directory) to see how it is configured (it pulls information from the `.env` file automatically on its launch).

## Running tests

We use [Codeception](https://codeception.com/). However, the vast majority of the application is uncovered by tests due to time pressure.

All tests are meant to be run with our Docker configuration. The application should, however, be launched differently:
```sh
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml up
```

This launches a Selenium driver alongside the application.

To shut everything down, execute the following:
```sh
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml down
```