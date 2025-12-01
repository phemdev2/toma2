<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Adjust these to control which frontends can call your API. In dev this
    | allows Vite/Next ports; in prod, set CORS_ALLOWED_ORIGINS in .env.
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    // Allow all HTTP verbs, or restrict as needed e.g. ['GET','POST','PUT','PATCH','DELETE','OPTIONS']
    'allowed_methods' => ['*'],

    // Comma-separated list in .env, e.g.:
    // CORS_ALLOWED_ORIGINS="http://localhost:5173,http://127.0.0.1:5173,http://localhost:3000,http://127.0.0.1:3000"
    'allowed_origins' => array_filter(array_map('trim', explode(
        ',',
        env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173,http://localhost:3000,http://127.0.0.1:3000')
    ))),

    // Usually keep empty unless you want to match by regex.
    'allowed_origins_patterns' => [],

    // Allow all headers in dev; lock down in prod if required.
    'allowed_headers' => ['*'],

    // Headers you want the browser to make visible to JS (rarely needed).
    'exposed_headers' => [],

    // How long (seconds) the preflight can be cached by the browser.
    'max_age' => 0,

    // Set to true if you’re using cookies/auth across origins (e.g., Sanctum SPA).
    // If true, you must NOT use '*' for allowed_origins — list them explicitly.
    'supports_credentials' => filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOLEAN),

];
