<?php

return [
    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'default',
                'queue' => ['default'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 3,
            ],
        ],
    ],
    // ... rest of horizon config ...
]; 