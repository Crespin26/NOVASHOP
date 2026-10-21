<?php

return [
    'enabled' => (bool) env('COMMERCE_ENABLED', false),
    'currency' => env('COMMERCE_CURRENCY', 'EUR'),
    'countries' => array_filter(explode(',', env('COMMERCE_COUNTRIES', ''))),
    'taxes_configured' => (bool) env('COMMERCE_TAXES_CONFIGURED', false),
    'prices_include_tax' => true,
    'account_deletion_requests' => true,
    'shipping' => (int) env('COMMERCE_SHIPPING_MINOR', 490),
    'free_shipping_threshold' => (int) env('COMMERCE_FREE_SHIPPING_MINOR', 10000),
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
    'returns_days' => (int) env('COMMERCE_RETURNS_DAYS', 0),
    'stripe_secret' => env('STRIPE_SECRET_KEY'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'payment_currencies' => array_filter(explode(',', env('COMMERCE_PAYMENT_CURRENCIES', ''))),
    'exchange_rates' => json_decode(env('COMMERCE_EXCHANGE_RATES', '{}'), true),
];
