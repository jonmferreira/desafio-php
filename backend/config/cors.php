<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Restrito ao domínio do frontend (Security Misconfiguration - API8).
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Idempotent-Replay'],

    'max_age' => 0,

    'supports_credentials' => false,

];
