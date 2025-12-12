const mix = require('laravel-mix');
const path = require('path')

mix.alias({
    '@': path.join(__dirname, 'resources/js')
})

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

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css');

if (!mix.inProduction()) {
    mix.browserSync({
        proxy: 'localhost',
        open: false,
        port: 8080,
        watchOptions: {
            interval: 500
        }
    });
    mix.sourceMaps()
}
