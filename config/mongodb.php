<?php

return [
    /*
    | Same Mongo server — only database name differs between dev/prod.
    | MySQL (DB_DATABASE) and Mongo names are often different in this project.
    */
    'indicator' => [
        'uri' => env('DB_MONGO_DEV', env('DB_MONGO_STOREDATA', env('DB_MONGO_STOREDATAB', env('DB_MONGO', '')))),
        // Do not fall back to DB_DATABASE — MySQL name != Mongo database name.
        'database' => env('MONGO_DATABASE', 'sosecure_threatintelligent_dev'),
    ],
];
