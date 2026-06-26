<?php

return [
    'name' => 'HoneypotCenter',

    'mongodb' => [
        // Active profile: dev | prod (switch here for all honeypot Mongo usage)
        'target' => env('HONEYPOT_MONGO_TARGET', 'dev'),

        'profiles' => [
            'dev' => [
                'uri' => env('HONEYPOT_MONGO_URI_DEV', env('HONEYPOT_MONGO_URI', env('DB_MONGO_DEV', ''))),
                'database' => env('HONEYPOT_MONGO_DATABASE_DEV', env('HONEYPOT_MONGO_DATABASE', 'sosecure_threatintelligent_dev')),
            ],
            'prod' => [
                'uri' => env('HONEYPOT_MONGO_URI_PROD', env('DB_MONGO_STOREDATA', env('DB_MONGO_STOREDATAB', ''))),
                'database' => env('HONEYPOT_MONGO_DATABASE_PROD', 'sosecure_threatintelligent'),
            ],
        ],

        'collections' => [
            'alerts' => env('HONEYPOT_COLLECTION_ALERTS', 'fx_honeypot_alerts'),
            'summaries' => env('HONEYPOT_COLLECTION_SUMMARIES', 'fx_honeypot_summaries'),
        ],

        'indicator_collections' => [
            'events' => env('HONEYPOT_INDICATOR_COLLECTION_EVENTS', 'fx_otx_events'),
            'indicator_detail' => env('HONEYPOT_INDICATOR_COLLECTION_DETAIL', 'fx_otx_indicator_detail'),
            'events_indicator_ref' => env('HONEYPOT_INDICATOR_COLLECTION_REF', 'fx_otx_events_indicator_ref'),
            'transaction_indicators_data' => env('HONEYPOT_INDICATOR_COLLECTION_DATA', 'fx_transaction_otx_indicators_data'),
            'otx_type' => env('HONEYPOT_INDICATOR_COLLECTION_TYPE', 'fx_otx_type'),
            'events_daily_stats' => env('HONEYPOT_INDICATOR_COLLECTION_DAILY_STATS', 'fx_events_daily_stats'),
        ],

        'ttl_days' => (int) env('HONEYPOT_TTL_DAYS', 30),
    ],

    'api' => [
        'token_type' => 'honeypot_agent',
        'header' => 'X-API-Key',
        'rate_limit' => (int) env('HONEYPOT_RATE_LIMIT', 120),
    ],

    'indicator_publish' => [
        'creator_org' => env('HONEYPOT_INDICATOR_CREATOR_ORG', 'Threat inSights'),
        'public' => (int) env('HONEYPOT_INDICATOR_PUBLIC', 0),
    ],
];
