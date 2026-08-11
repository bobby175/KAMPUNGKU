<?php

return [
    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
    'api_key' => env('GOOGLE_DRIVE_API_KEY'),
    'ca_bundle' => env('GOOGLE_DRIVE_CA_BUNDLE'),
    'credentials_path' => env('GOOGLE_DRIVE_CREDENTIALS_PATH', 'storage/app/private/google-drive-service-account.json'),
    'cache_seconds' => (int) env('GOOGLE_DRIVE_CACHE_SECONDS', 300),
];
