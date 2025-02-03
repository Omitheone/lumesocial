<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the OpenAI integration.
    | The API key should be set in your services.php config file.
    |
    */

    'default_model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),

    'models' => [
        'gpt-3.5-turbo' => [
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'top_p' => 1,
        ],
        'gpt-4' => [
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'top_p' => 1,
        ],
    ],

    'content_generation' => [
        'max_retries' => 3,
        'retry_delay' => 60, // seconds
        'timeout' => 30, // seconds
    ],

    'content_review' => [
        'max_retries' => 3,
        'retry_delay' => 60, // seconds
        'timeout' => 30, // seconds
    ],
]; 