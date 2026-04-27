<?php

return [
    'name' => 'IocFeed',
    'mongodb' => [
        'database' => 'sosecure_threatintelligent_dev',
        'collections' => [
            'feeds' => 'fx_ioc_feeds',
            'whitelists' => 'fx_ioc_whitelists',
            'audit_logs' => 'fx_ioc_audit_logs',
            'export_logs' => 'fx_ioc_export_logs',
        ],
        'export_token' => env('IOC_FEED_TOKEN', 'your-default-secret-token'),
    ],
];
