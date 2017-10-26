let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .autoload({}) //disable autoload of jquery, which we do not use and therefore do not need
    .js('resources/assets/js/app.jsx', 'public/js')
    .sass('resources/assets/css/app.scss', 'public/css')
    .sourceMaps()
    .version();
