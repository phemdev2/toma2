const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/cart.js', 'public/js')
   .js('resources/js/product.js', 'public/js')
   .css('resources/css/app.css', 'public/css')
   .sass('resources/sass/styles.scss', 'public/css')
   .sass('resources/sass/responsive.scss', 'public/css')
   .version(); // Cache-busting
