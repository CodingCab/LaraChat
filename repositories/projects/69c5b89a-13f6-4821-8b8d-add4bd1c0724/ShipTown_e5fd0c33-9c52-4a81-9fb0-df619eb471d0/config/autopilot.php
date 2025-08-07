<?php

return [
    'config_key_name' => env('PACKING_DAILY_MAX_CONFIG_KEY_NAME', 'packing_daily_max'),
    'key_names' => [
        'max_order_age_allowed' => env('MAX_ORDER_AGE_ALLOWED_KEY_NAME', 'max_order_age_allowed'),
    ],
];
