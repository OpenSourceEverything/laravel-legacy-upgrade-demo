<?php

return [
    // Laravel 5.6 moved logging out of config/app.php. Keep the existing file.
    'default' => 'single',
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('APP_LOG_LEVEL', 'warning'),
        ],
    ],
];
