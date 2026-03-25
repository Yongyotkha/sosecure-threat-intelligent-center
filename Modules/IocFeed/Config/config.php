<?php

return [
    'name' => 'IocFeed',
    'mongodb' => [
        'database' => 'sosecure_threatintelligent_dev',
        'collections' => [
            'feeds' => 'fx_ioc_feeds',
            'whitelists' => 'fx_ioc_whitelists',
            'audit_logs' => 'fx_ioc_audit_logs',
        ],
    ],
];
