<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file defines the settings for file uploads in the
    | application. It includes options for chunk size, encryption, and
    | storage paths.
    |
    */

    'default_encryption_key' => env('UPLOAD_DEFAULT_ENCRYPTION_KEY'),
    'chunk_size' => 1024,
    'cipher' => 'AES-256-CBC',
];
