<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Cloud Messaging (FCM) push notifications.
    | This uses the Firebase HTTP v1 API with service account authentication.
    |
    */

    /**
     * Path to Firebase service account credentials JSON file
     */
    'credentials' => env('FCM_CREDENTIALS', config_path('credentials.json')),

    /**
     * Firebase Cloud Messaging API endpoint
     */
    'api_url' => 'https://fcm.googleapis.com/v1/projects/'.env('FCM_PROJECT_ID', 'language-ai-458304').'/messages:send',

    /**
     * OAuth2 token URI for service account authentication
     */
    'token_uri' => 'https://oauth2.googleapis.com/token',

    /**
     * OAuth2 scope for FCM
     */
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',

    /**
     * Default notification configuration
     */
    'notification' => [
        'priority' => env('FCM_PRIORITY', 'high'), // high or normal
        'ttl' => env('FCM_TTL', 3600), // Time to live in seconds
    ],

    /**
     * Logging configuration
     */
    'logging' => [
        'enabled' => env('FCM_LOGGING_ENABLED', true),
        'channel' => env('FCM_LOGGING_CHANNEL', 'daily'),
    ],

];
