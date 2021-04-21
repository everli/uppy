const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');


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

mix.sass('resources/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false,
        postCss: [tailwindcss('tailwind.config.js')],
    })
    .js('resources/js/app.js', 'public/js').vue({ version: 2 })
    .scripts([
        'node_modules/marked/marked.min.js',
    ], 'public/js/vendor.js');

if (mix.inProduction()) {
    mix.version().disableNotifications();
}
