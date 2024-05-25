<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Cloudinary settings. You can obtain your
    | Cloudinary credentials from the Cloudinary dashboard.
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the default upload options for Cloudinary.
    | These options will be applied to all uploads by default.
    |
    */

    'upload' => [
        'folder' => 'reborn', // Default folder for uploaded files
        'overwrite' => true,   // Overwrite files with the same public ID
    ],
];
