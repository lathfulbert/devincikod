<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */
    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'path' => 'storage/uploads',
        'public_url' => 'uploads', // Relative to public folder
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/zip',
            'text/plain',
            'text/csv'
        ],
        'allowed_extensions' => [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'pdf',
            'zip',
            'txt',
            'csv'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */
    'images' => [
        'create_thumbnails' => true,
        'thumbnail_path' => 'storage/uploads/thumbs',
        'thumbnail_dimensions' => [
            'width' => 150,
            'height' => 150
        ],
        'quality' => 80, // JPEG quality
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    */
    'organize_by_date' => true, // Create subfolders Y/m/d
    'organize_by_user' => false, // Create subfolders by user ID
];
