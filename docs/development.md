# Development

`master` branch contains built CSS and JS assets, whereas `dev` branch does not.

## Building Assets

[Webpack 4](https://webpack.js.org/) is used here. These are the provided commands:

```bash
# Building for development:
$ npm run dev

# Building for development and watching for file changes:
$ npm run watch

# Building for production:
$ npm run prod
```

## Filling the Database With Dummy Content

!> If you are using provided Docker configuration, the [database access caveat](installation.md#database-access-caveat) applies here!

```bash
$ php artisan db:seed
```

By doing this, `storage/search/users.index` and `storage/search/offers.index` files may have been created.
Ensure that they are both readable and writable by the web server. (This is ensured automatically if you are using provided Docker configuration.)

## Running Tests

[Codeception](https://codeception.com/) is used here. However, the vast majority of the application is uncovered by tests due to time pressure.

All tests are meant to be run with our Docker configuration. The application should, however, be launched differently:

```bash
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml up
```

This command launches a [Selenium](https://www.seleniumhq.org/) driver alongside the application.

To shut everything down, execute the following:

```bash
$ docker-compose -f docker-compose.yml -f docker-compose.testing.yml down
```