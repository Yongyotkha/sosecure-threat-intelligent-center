<?php return array (
  'app' => 
  array (
    'name' => 'Threat-Intelligent-Center',
    'env' => 'local',
    'debug' => true,
    'url' => 'https://example.com',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'key' => 'base64:Wjyyebus+I2W4pFRoxl0RnHpj/VyjUyDEh/dqqvWaF0=',
    'cipher' => 'AES-256-CBC',
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Spatie\\TranslationLoader\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'Laravel\\Tinker\\TinkerServiceProvider',
      23 => 'App\\Providers\\AppServiceProvider',
      24 => 'App\\Providers\\AuthServiceProvider',
      25 => 'App\\Providers\\BroadcastServiceProvider',
      26 => 'App\\Providers\\EventServiceProvider',
      27 => 'App\\Providers\\RouteServiceProvider',
      28 => 'App\\Providers\\CustomBladeServiceProvider',
      29 => 'App\\Providers\\ToastrServiceProvider',
      30 => 'App\\Providers\\BladeSvgServiceProvider',
      31 => 'App\\Providers\\CookieConsentServiceProvider',
      32 => 'App\\Providers\\DropboxServiceProvider',
      33 => 'App\\Providers\\ExtrasServiceProvider',
      34 => 'App\\Providers\\CaptchaServiceProvider',
      35 => 'Intervention\\Image\\ImageServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Redis' => 'Illuminate\\Support\\Facades\\Redis',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Role' => 'Spatie\\Permission\\Models\\Role',
      'OAuth' => 'Artdarek\\OAuth\\Facade\\OAuth',
      'Toastr' => 'App\\Facades\\Toastr',
      'NoCaptcha' => 'App\\Facades\\NoCaptcha',
      'Image' => 'Intervention\\Image\\Facades\\Image',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'api' => 
      array (
        'driver' => 'jwt',
        'provider' => 'users',
        'hash' => false,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'Modules\\Users\\Entities\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_resets',
        'expire' => 60,
      ),
    ),
    'passport' => 
    array (
      'cookie' => 'workice_crm_token',
    ),
  ),
  'backup' => 
  array (
    'backup' => 
    array (
      'name' => 'Threat-Intelligent-Center',
      'source' => 
      array (
        'files' => 
        array (
          'include' => 
          array (
            0 => 'C:\\xampp\\htdocs\\threat-intelligent-center',
          ),
          'exclude' => 
          array (
            0 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\vendor',
            1 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\node_modules',
          ),
          'followLinks' => false,
        ),
        'databases' => 
        array (
          0 => 'mysql',
        ),
      ),
      'gzip_database_dump' => false,
      'destination' => 
      array (
        'filename_prefix' => '',
        'disks' => 
        array (
          0 => 'local',
        ),
      ),
    ),
    'notifications' => 
    array (
      'notifications' => 
      array (
        'Spatie\\Backup\\Notifications\\Notifications\\BackupHasFailed' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\UnhealthyBackupWasFound' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\CleanupHasFailed' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\BackupWasSuccessful' => 
        array (
          0 => 'mail',
          1 => 'slack',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\HealthyBackupWasFound' => 
        array (
          0 => 'mail',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\CleanupWasSuccessful' => 
        array (
          0 => 'mail',
          1 => 'slack',
        ),
      ),
      'notifiable' => 'Spatie\\Backup\\Notifications\\Notifiable',
      'mail' => 
      array (
        'to' => 'backups@example.com',
      ),
      'slack' => 
      array (
        'webhook_url' => '',
        'channel' => '',
      ),
    ),
    'monitorBackups' => 
    array (
      0 => 
      array (
        'name' => 'Threat-Intelligent-Center',
        'disks' => 
        array (
          0 => 'local',
        ),
        'newestBackupsShouldNotBeOlderThanDays' => 1,
        'storageUsedMayNotBeHigherThanMegabytes' => 5000,
      ),
    ),
    'cleanup' => 
    array (
      'strategy' => 'Spatie\\Backup\\Tasks\\Cleanup\\Strategies\\DefaultStrategy',
      'defaultStrategy' => 
      array (
        'keepAllBackupsForDays' => 7,
        'keepDailyBackupsForDays' => 16,
        'keepWeeklyBackupsForWeeks' => 8,
        'keepMonthlyBackupsForMonths' => 4,
        'keepYearlyBackupsForYears' => 2,
        'deleteOldestBackupsWhenUsingMoreMegabytesThan' => 300,
      ),
    ),
  ),
  'blade-svg' => 
  array (
    'svg_path' => 'resources/assets/svg',
    'spritesheet_path' => 'resources/assets/svg/spritesheet.svg',
    'spritesheet_url' => '',
    'inline' => true,
    'class' => 'svg-inline--fa',
  ),
  'broadcasting' => 
  array (
    'default' => 'pusher',
    'connections' => 
    array (
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => '',
        'secret' => '',
        'app_id' => '',
        'options' => 
        array (
          'cluster' => 'ap2',
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'file',
    'stores' => 
    array (
      'apc' => 
      array (
        'driver' => 'apc',
      ),
      'array' => 
      array (
        'driver' => 'array',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
    ),
    'prefix' => 'workice',
  ),
  'captcha' => 
  array (
    'secret' => '',
    'sitekey' => '',
    'options' => 
    array (
      'timeout' => 30,
    ),
  ),
  'cookie-consent' => 
  array (
    'enabled' => true,
    'cookie_name' => 'workice_cookie_consent',
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'database' => 'sosecure_threatintelligent_dev',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'host' => 'db-mysql-sgp1-sosecure-do-user-5073486-0.a.db.ondigitalocean.com',
        'port' => '25060',
        'database' => 'sosecure_threatintelligent_dev',
        'username' => 'threatintelligent',
        'password' => 'dbe0ujzsb4b7nh13',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'fx_',
        'prefix_indexes' => true,
        'strict' => false,
        'engine' => NULL,
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'host' => 'db-mysql-sgp1-sosecure-do-user-5073486-0.a.db.ondigitalocean.com',
        'port' => '25060',
        'database' => 'sosecure_threatintelligent_dev',
        'username' => 'threatintelligent',
        'password' => 'dbe0ujzsb4b7nh13',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'host' => 'db-mysql-sgp1-sosecure-do-user-5073486-0.a.db.ondigitalocean.com',
        'port' => '25060',
        'database' => 'sosecure_threatintelligent_dev',
        'username' => 'threatintelligent',
        'password' => 'dbe0ujzsb4b7nh13',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'predis',
      'default' => 
      array (
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => '6379',
        'database' => 0,
      ),
      'cache' => 
      array (
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => '6379',
        'database' => 1,
      ),
    ),
  ),
  'datatables' => 
  array (
    'search' => 
    array (
      'smart' => true,
      'multi_term' => true,
      'case_insensitive' => true,
      'use_wildcards' => false,
    ),
    'index_column' => 'DT_Row_Index',
    'engines' => 
    array (
      'eloquent' => 'Yajra\\DataTables\\EloquentDataTable',
      'query' => 'Yajra\\DataTables\\QueryDataTable',
      'collection' => 'Yajra\\DataTables\\CollectionDataTable',
    ),
    'builders' => 
    array (
      'Illuminate\\Database\\Eloquent\\Relations\\Relation' => 'eloquent',
      'Illuminate\\Database\\Eloquent\\Builder' => 'eloquent',
      'Illuminate\\Database\\Query\\Builder' => 'query',
      'Illuminate\\Support\\Collection' => 'collection',
    ),
    'nulls_last_sql' => '%s %s NULLS LAST',
    'error' => NULL,
    'columns' => 
    array (
      'excess' => 
      array (
        0 => 'rn',
        1 => 'row_num',
      ),
      'escape' => '*',
      'raw' => 
      array (
        0 => 'action',
      ),
      'blacklist' => 
      array (
        0 => 'password',
        1 => 'remember_token',
      ),
      'whitelist' => '*',
    ),
    'json' => 
    array (
      'header' => 
      array (
      ),
      'options' => 0,
    ),
  ),
  'db-fields' => 
  array (
    'invoice' => 
    array (
      0 => 'reference_no',
      1 => 'title',
      2 => 'client_id',
      3 => 'due_date',
      4 => 'tax',
      5 => 'tax2',
      6 => 'discount',
      7 => 'currency',
      8 => 'discount_percent',
      9 => 'notes',
    ),
    'contact' => 
    array (
      0 => 'email',
      1 => 'name',
      2 => 'company',
      3 => 'job_title',
      4 => 'city',
      5 => 'country',
      6 => 'phone',
      7 => 'mobile',
      8 => 'skype',
      9 => 'twitter',
      10 => 'language',
      11 => 'address',
      12 => 'state',
      13 => 'zip_code',
      14 => 'website',
    ),
    'items' => 
    array (
      0 => 'name',
      1 => 'description',
      2 => 'quantity',
      3 => 'unit_cost',
      4 => 'tax_rate',
    ),
    'client' => 
    array (
      0 => 'name',
      1 => 'email',
      2 => 'contact_person',
      3 => 'contact_email',
      4 => 'phone',
      5 => 'mobile',
      6 => 'address1',
      7 => 'address2',
      8 => 'city',
      9 => 'state',
      10 => 'zip_code',
      11 => 'country',
      12 => 'tax_number',
      13 => 'currency',
    ),
    'lead' => 
    array (
      0 => 'name',
      1 => 'source',
      2 => 'stage',
      3 => 'job_title',
      4 => 'company',
      5 => 'phone',
      6 => 'mobile',
      7 => 'email',
      8 => 'address',
      9 => 'address1',
      10 => 'address2',
      11 => 'city',
      12 => 'state',
      13 => 'zip',
      14 => 'country',
      15 => 'timezone',
      16 => 'website',
      17 => 'skype',
      18 => 'facebook',
      19 => 'twitter',
      20 => 'linkedin',
      21 => 'lead_score',
      22 => 'lead_value',
    ),
    'deal' => 
    array (
      0 => 'title',
      1 => 'stage',
      2 => 'source',
      3 => 'pipeline',
      4 => 'currency',
      5 => 'deal_value',
      6 => 'contact_person',
      7 => 'organization',
      8 => 'due_date',
      9 => 'status',
      10 => 'won_time',
      11 => 'lost_time',
      12 => 'lost_reason',
    ),
    'estimate' => 
    array (
      0 => 'reference_no',
      1 => 'title',
      2 => 'client_id',
      3 => 'due_date',
      4 => 'tax',
      5 => 'tax2',
      6 => 'discount',
      7 => 'currency',
      8 => 'discount_percent',
      9 => 'notes',
      10 => 'status',
    ),
    'expense' => 
    array (
      0 => 'code',
      1 => 'expense_date',
      2 => 'client_id',
      3 => 'project_id',
      4 => 'currency',
      5 => 'billable',
      6 => 'amount',
      7 => 'category',
      8 => 'tax',
      9 => 'tax2',
      10 => 'notes',
      11 => 'invoiced',
    ),
  ),
  'debugbar' => 
  array (
    'enabled' => false,
    'except' => 
    array (
      0 => 'telescope*',
      1 => 'horizon*',
    ),
    'storage' => 
    array (
      'enabled' => true,
      'driver' => 'file',
      'path' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\debugbar',
      'connection' => NULL,
      'provider' => '',
    ),
    'include_vendors' => true,
    'capture_ajax' => true,
    'add_ajax_timing' => false,
    'error_handler' => false,
    'clockwork' => false,
    'collectors' => 
    array (
      'phpinfo' => true,
      'messages' => true,
      'time' => true,
      'memory' => true,
      'exceptions' => true,
      'log' => true,
      'db' => true,
      'views' => true,
      'route' => true,
      'auth' => true,
      'gate' => true,
      'session' => true,
      'symfony_request' => true,
      'mail' => true,
      'laravel' => false,
      'events' => false,
      'default_request' => false,
      'logs' => false,
      'files' => false,
      'config' => false,
    ),
    'options' => 
    array (
      'auth' => 
      array (
        'show_name' => true,
      ),
      'db' => 
      array (
        'with_params' => true,
        'backtrace' => true,
        'timeline' => false,
        'explain' => 
        array (
          'enabled' => false,
          'types' => 
          array (
            0 => 'SELECT',
          ),
        ),
        'hints' => true,
      ),
      'mail' => 
      array (
        'full_log' => false,
      ),
      'views' => 
      array (
        'data' => false,
      ),
      'route' => 
      array (
        'label' => true,
      ),
      'logs' => 
      array (
        'file' => NULL,
      ),
    ),
    'inject' => true,
    'route_prefix' => '_debugbar',
    'route_domain' => NULL,
    'theme' => 'auto',
  ),
  'dompdf' => 
  array (
    'show_warnings' => false,
    'orientation' => 'portrait',
    'defines' => 
    array (
      'font_dir' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\app/public/fonts/',
      'font_cache' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\app/public/fonts/',
      'temp_dir' => 'C:\\Users\\Intel\\AppData\\Local\\Temp',
      'chroot' => 'C:\\xampp\\htdocs\\threat-intelligent-center',
      'enable_font_subsetting' => false,
      'pdf_backend' => 'CPDF',
      'default_media_type' => 'screen',
      'default_paper_size' => 'a4',
      'default_font' => 'serif',
      'dpi' => 96,
      'enable_php' => false,
      'enable_javascript' => true,
      'enable_remote' => true,
      'font_height_ratio' => 1.1,
      'enable_html5_parser' => false,
    ),
  ),
  'emojione' => 
  array (
    'imagePathPNG' => NULL,
    'sprites' => false,
    'spriteSize' => 32,
    'emojiSize' => 64,
    'emojiVersion' => '3.1',
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'temp_path' => 'C:\\Users\\Intel\\AppData\\Local\\Temp',
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
      ),
    ),
    'imports' => 
    array (
      'read_only' => true,
      'ignore_empty' => false,
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'escape_character' => '\\',
        'contiguous' => false,
        'input_encoding' => 'UTF-8',
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
    ),
    'transactions' => 
    array (
      'handler' => 'db',
    ),
    'temporary_files' => 
    array (
      'local_path' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\framework/laravel-excel',
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'cloud' => 's3',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\app',
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\app/public',
        'url' => 'https://example.com/storage',
        'visibility' => 'public',
      ),
      's3' => 
      array (
        'driver' => 's3',
        'url' => '',
        'key' => '',
        'secret' => '',
        'region' => '',
        'bucket' => '',
      ),
      'dropbox' => 
      array (
        'driver' => 'dropbox',
        'authorizationToken' => '',
      ),
      'ftp' => 
      array (
        'driver' => 'ftp',
        'host' => 'ftp.example.com',
        'username' => 'your-username',
        'password' => 'your-password',
        'port' => 21,
        'root' => '',
        'passive' => true,
        'ssl' => true,
        'timeout' => 30,
      ),
      'sftp' => 
      array (
        'driver' => 'sftp',
        'host' => 'example.com',
        'username' => 'your-username',
        'password' => 'your-password',
        'port' => 22,
        'root' => '',
        'timeout' => 30,
      ),
      'rackspace' => 
      array (
        'driver' => 'rackspace',
        'username' => 'your-username',
        'key' => 'your-key',
        'container' => 'your-container',
        'endpoint' => 'https://identity.api.rackspacecloud.com/v2.0/',
        'region' => 'IAD',
        'url_type' => 'publicURL',
      ),
    ),
  ),
  'google2fa' => 
  array (
    'enabled' => true,
    'lifetime' => 10080,
    'keep_alive' => true,
    'auth' => 'auth',
    'guard' => '',
    'session_var' => 'workice_2fa',
    'otp_input' => 'one_time_password',
    'window' => 1,
    'forbid_old_passwords' => false,
    'otp_secret_column' => 'google2fa_secret',
    'view' => 'google2fa.index',
    'error_messages' => 
    array (
      'wrong_otp' => 'Invalid 2FA token, please try again',
    ),
    'throw_exceptions' => true,
    'qrcode_image_backend' => 'imagemagick',
  ),
  'gravatar' => 
  array (
    'default' => 
    array (
      'size' => 80,
      'fallback' => 'mm',
      'secure' => false,
      'maximumRating' => 'g',
      'forceDefault' => false,
      'forceExtension' => 'jpg',
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 10,
    ),
    'argon' => 
    array (
      'memory' => 1024,
      'threads' => 2,
      'time' => 2,
    ),
  ),
  'horizon' => 
  array (
    'use' => 'default',
    'prefix' => 'horizon:',
    'waits' => 
    array (
      'redis:default' => 60,
    ),
    'trim' => 
    array (
      'recent' => 60,
      'failed' => 10080,
    ),
    'environments' => 
    array (
      'production' => 
      array (
        'supervisor-1' => 
        array (
          'connection' => 'redis',
          'queue' => 
          array (
            0 => 'default',
          ),
          'balance' => 'simple',
          'processes' => 10,
          'tries' => 3,
        ),
      ),
      'local' => 
      array (
        'supervisor-1' => 
        array (
          'connection' => 'redis',
          'queue' => 
          array (
            0 => 'default',
          ),
          'balance' => 'simple',
          'processes' => 3,
          'tries' => 3,
        ),
      ),
    ),
  ),
  'image' => 
  array (
    'driver' => 'gd',
  ),
  'installer' => 
  array (
    'name' => 'Installer',
    'core' => 
    array (
      'minPhpVersion' => '7.1.3',
    ),
    'requirements' => 
    array (
      'php' => 
      array (
        0 => 'openssl',
        1 => 'pdo',
        2 => 'mbstring',
        3 => 'tokenizer',
        4 => 'JSON',
        5 => 'cURL',
        6 => 'mysqli',
        7 => 'imap',
        8 => 'zip',
        9 => 'gd',
      ),
      'apache' => 
      array (
        0 => 'mod_rewrite',
      ),
    ),
    'permissions' => 
    array (
      'storage/app/' => '775',
      'storage/framework/' => '775',
      'storage/logs/' => '775',
      'bootstrap/cache/' => '775',
    ),
    'environment' => 
    array (
      'form' => 
      array (
        'rules' => 
        array (
          'app_name' => 'required|string|max:50',
          'environment' => 'sometimes|string|max:50',
          'environment_custom' => 'required_if:environment,other|max:50',
          'app_debug' => 
          array (
            0 => 'sometimes',
          ),
          'user.email' => 'required|email',
          'user.password' => 'required',
          'user.name' => 'required|string:max:50',
          'app_log_level' => 'sometimes|string|max:50',
          'app_url' => 'required|url',
          'database_connection' => 'required|string|max:50',
          'database_hostname' => 'required|string|max:500',
          'database_port' => 'required|numeric',
          'database_name' => 'required|string|max:50',
          'database_username' => 'required|string|max:50',
          'database_password' => 'required|string|max:50',
          'broadcast_driver' => 'sometimes|string|max:50',
          'cache_driver' => 'sometimes|string|max:50',
          'session_driver' => 'sometimes|string|max:50',
          'queue_connection' => 'sometimes|string|max:50',
          'redis_hostname' => 'sometimes|string|max:50',
          'redis_password' => 'sometimes|string|max:50',
          'redis_port' => 'sometimes|numeric',
          'mail_driver' => 'sometimes|string|max:50',
          'mail_host' => 'sometimes|string|max:50',
          'mail_port' => 'sometimes|string|max:50',
          'mail_username' => 'sometimes|string|max:50',
          'mail_password' => 'sometimes|string|max:50',
          'mail_encryption' => 'sometimes|string|max:50',
          'pusher_app_id' => 'max:50',
          'pusher_app_key' => 'max:50',
          'pusher_app_secret' => 'max:50',
        ),
      ),
    ),
    'installed' => 
    array (
      'redirectOptions' => 
      array (
        'route' => 
        array (
          'name' => 'welcome',
          'data' => 
          array (
          ),
        ),
        'abort' => 
        array (
          'type' => '404',
        ),
        'dump' => 
        array (
          'data' => 'Dumping a not found message.',
        ),
      ),
    ),
    'installedAlreadyAction' => '',
    'updaterEnabled' => 'true',
  ),
  'jwt' => 
  array (
    'secret' => '6lOeXaaUf8s6iIv7IWXAMK4SxQ0LXoTpPxSMqOis7rFrtxQyAslvPGuxh8Ew9Gsa',
    'keys' => 
    array (
      'public' => NULL,
      'private' => NULL,
      'passphrase' => NULL,
    ),
    'ttl' => 60,
    'refresh_ttl' => 20160,
    'algo' => 'HS256',
    'required_claims' => 
    array (
      0 => 'iss',
      1 => 'iat',
      2 => 'exp',
      3 => 'nbf',
      4 => 'sub',
      5 => 'jti',
    ),
    'persistent_claims' => 
    array (
    ),
    'lock_subject' => true,
    'leeway' => 0,
    'blacklist_enabled' => true,
    'blacklist_grace_period' => 0,
    'decrypt_cookies' => false,
    'providers' => 
    array (
      'jwt' => 'Tymon\\JWTAuth\\Providers\\JWT\\Lcobucci',
      'auth' => 'Tymon\\JWTAuth\\Providers\\Auth\\Illuminate',
      'storage' => 'Tymon\\JWTAuth\\Providers\\Storage\\Illuminate',
    ),
  ),
  'laravel-page-speed' => 
  array (
    'enable' => true,
    'skip' => 
    array (
      0 => '*.xml',
      1 => '*.less',
      2 => '*.pdf',
      3 => '*.doc',
      4 => '*.txt',
      5 => '*.ico',
      6 => '*.rss',
      7 => '*.zip',
      8 => '*.mp3',
      9 => '*.rar',
      10 => '*.exe',
      11 => '*.wmv',
      12 => '*.doc',
      13 => '*.avi',
      14 => '*.ppt',
      15 => '*.mpg',
      16 => '*.mpeg',
      17 => '*.tif',
      18 => '*.wav',
      19 => '*.mov',
      20 => '*.psd',
      21 => '*.ai',
      22 => '*.xls',
      23 => '*.mp4',
      24 => '*.m4a',
      25 => '*.swf',
      26 => '*.dat',
      27 => '*.dmg',
      28 => '*.iso',
      29 => '*.flv',
      30 => '*.m4v',
      31 => '*.torrent',
      32 => '*/send/*',
      33 => '*/pdf/*',
      34 => 'items/insert/*',
      35 => '_debugbar/*',
      36 => '*/profile',
      37 => 'contracts/view/*',
      38 => 'knowledgebase/view/*',
      39 => '*/create',
      40 => 'settings/payments',
      41 => '*/edit/*',
      42 => '*/download/*',
    ),
    'php' => 
    array (
      'enable' => true,
      'skip' => 
      array (
        0 => '*.xml',
        1 => '*.less',
        2 => '*.pdf',
        3 => '*.doc',
        4 => '*.txt',
        5 => '*.ico',
        6 => '*.rss',
        7 => '*.zip',
        8 => '*.mp3',
        9 => '*.rar',
        10 => '*.exe',
        11 => '*.wmv',
        12 => '*.doc',
        13 => '*.avi',
        14 => '*.ppt',
        15 => '*.mpg',
        16 => '*.mpeg',
        17 => '*.tif',
        18 => '*.wav',
        19 => '*.mov',
        20 => '*.psd',
        21 => '*.ai',
        22 => '*.xls',
        23 => '*.mp4',
        24 => '*.m4a',
        25 => '*.swf',
        26 => '*.dat',
        27 => '*.dmg',
        28 => '*.iso',
        29 => '*.flv',
        30 => '*.m4v',
        31 => '*.torrent',
      ),
    ),
  ),
  'laravel-widgets' => 
  array (
    'use_jquery_for_ajax_calls' => true,
    'route_middleware' => 
    array (
      0 => 'web',
    ),
    'widget_stub' => 'vendor/arrilot/laravel-widgets/src/Console/stubs/widget.stub',
    'widget_plain_stub' => 'vendor/arrilot/laravel-widgets/src/Console/stubs/widget_plain.stub',
  ),
  'laravolt' => 
  array (
    'avatar' => 
    array (
      'driver' => 'gd',
      'generator' => 'Laravolt\\Avatar\\Generator\\DefaultGenerator',
      'ascii' => false,
      'shape' => 'circle',
      'width' => 256,
      'height' => 256,
      'chars' => 2,
      'fontSize' => 80,
      'uppercase' => true,
      'fonts' => 
      array (
        0 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\config\\laravolt/../fonts/OpenSans-Bold.ttf',
        1 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\config\\laravolt/../fonts/rockwell.ttf',
      ),
      'foregrounds' => 
      array (
        0 => '#FFFFFF',
      ),
      'backgrounds' => 
      array (
        0 => '#f44336',
        1 => '#E91E63',
        2 => '#9C27B0',
        3 => '#673AB7',
        4 => '#3F51B5',
        5 => '#2196F3',
        6 => '#03A9F4',
        7 => '#00BCD4',
        8 => '#009688',
        9 => '#4CAF50',
        10 => '#8BC34A',
        11 => '#CDDC39',
        12 => '#FFC107',
        13 => '#FF9800',
        14 => '#FF5722',
      ),
      'border' => 
      array (
        'size' => 1,
        'color' => 'foreground',
      ),
    ),
  ),
  'logging' => 
  array (
    'default' => 'daily',
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'daily',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\logs/laravel.log',
        'level' => 'debug',
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Workice Log',
        'emoji' => ':boom:',
        'level' => 'critical',
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
      ),
    ),
  ),
  'mail' => 
  array (
    'driver' => 'smtp',
    'host' => 'smtp.office365.com',
    'port' => '587',
    'from' => 
    array (
      'address' => 'no-reply@sosecure.co.th',
      'name' => 'no-reply@sosecure.co.th',
    ),
    'encryption' => 'tls',
    'username' => 'no-reply@sosecure.co.th',
    'password' => 'O3y6bfz2PxCvWT9HDTRC',
    'sendmail' => '/usr/sbin/sendmail -bs',
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\resources\\views/vendor/mail',
      ),
    ),
    'log_channel' => NULL,
  ),
  'modules' => 
  array (
    'namespace' => 'Modules',
    'stubs' => 
    array (
      'enabled' => false,
      'path' => 'C:\\xampp\\htdocs\\threat-intelligent-center/vendor/nwidart/laravel-modules/src/Commands/stubs',
      'files' => 
      array (
        'routes/web' => 'Routes/web.php',
        'routes/api' => 'Routes/api.php',
        'views/index' => 'Resources/views/index.blade.php',
        'views/master' => 'Resources/views/layouts/master.blade.php',
        'scaffold/config' => 'Config/config.php',
        'composer' => 'composer.json',
        'assets/js/app' => 'Resources/assets/js/app.js',
        'assets/sass/app' => 'Resources/assets/sass/app.scss',
        'webpack' => 'webpack.mix.js',
        'package' => 'package.json',
      ),
      'replacements' => 
      array (
        'routes/web' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'routes/api' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'webpack' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'json' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'MODULE_NAMESPACE',
        ),
        'views/index' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'views/master' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'scaffold/config' => 
        array (
          0 => 'STUDLY_NAME',
        ),
        'composer' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'VENDOR',
          3 => 'AUTHOR_NAME',
          4 => 'AUTHOR_EMAIL',
          5 => 'MODULE_NAMESPACE',
        ),
      ),
      'gitkeep' => true,
    ),
    'paths' => 
    array (
      'modules' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\Modules',
      'assets' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\public\\modules',
      'migration' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\database/migrations',
      'generator' => 
      array (
        'config' => 
        array (
          'path' => 'Config',
          'generate' => true,
        ),
        'command' => 
        array (
          'path' => 'Console',
          'generate' => true,
        ),
        'migration' => 
        array (
          'path' => 'Database/Migrations',
          'generate' => true,
        ),
        'seeder' => 
        array (
          'path' => 'Database/Seeders',
          'generate' => true,
        ),
        'factory' => 
        array (
          'path' => 'Database/factories',
          'generate' => true,
        ),
        'model' => 
        array (
          'path' => 'Entities',
          'generate' => true,
        ),
        'controller' => 
        array (
          'path' => 'Http/Controllers',
          'generate' => true,
        ),
        'filter' => 
        array (
          'path' => 'Http/Middleware',
          'generate' => true,
        ),
        'request' => 
        array (
          'path' => 'Http/Requests',
          'generate' => true,
        ),
        'provider' => 
        array (
          'path' => 'Providers',
          'generate' => true,
        ),
        'assets' => 
        array (
          'path' => 'Resources/assets',
          'generate' => true,
        ),
        'lang' => 
        array (
          'path' => 'Resources/lang',
          'generate' => true,
        ),
        'views' => 
        array (
          'path' => 'Resources/views',
          'generate' => true,
        ),
        'test' => 
        array (
          'path' => 'Tests',
          'generate' => true,
        ),
        'repository' => 
        array (
          'path' => 'Repositories',
          'generate' => false,
        ),
        'event' => 
        array (
          'path' => 'Events',
          'generate' => false,
        ),
        'listener' => 
        array (
          'path' => 'Listeners',
          'generate' => false,
        ),
        'policies' => 
        array (
          'path' => 'Policies',
          'generate' => false,
        ),
        'rules' => 
        array (
          'path' => 'Rules',
          'generate' => false,
        ),
        'jobs' => 
        array (
          'path' => 'Jobs',
          'generate' => false,
        ),
        'emails' => 
        array (
          'path' => 'Emails',
          'generate' => false,
        ),
        'notifications' => 
        array (
          'path' => 'Notifications',
          'generate' => false,
        ),
        'resource' => 
        array (
          'path' => 'Transformers',
          'generate' => false,
        ),
      ),
    ),
    'scan' => 
    array (
      'enabled' => false,
      'paths' => 
      array (
        0 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\vendor/*/*',
      ),
    ),
    'composer' => 
    array (
      'vendor' => 'nwidart',
      'author' => 
      array (
        'name' => 'Nicolas Widart',
        'email' => 'n.widart@gmail.com',
      ),
    ),
    'cache' => 
    array (
      'enabled' => false,
      'key' => 'laravel-modules',
      'lifetime' => 60,
    ),
    'register' => 
    array (
      'translations' => true,
      'files' => 'register',
    ),
  ),
  'mollie' => 
  array (
    'key' => '',
    'methods' => 
    array (
      0 => 'creditcard',
    ),
  ),
  'mysql-restore' => 
  array (
    'mysql' => 
    array (
      'mysql_path' => 'mysql',
      'mysqldump_path' => 'mysqldump',
      'compress' => false,
      'local-storage' => 
      array (
        'disk' => 'local',
        'path' => 'backups',
      ),
      'cloud-storage' => 
      array (
        'enabled' => false,
        'disk' => 's3',
        'path' => 'path/to/your/backup-folder/',
        'keep-local' => true,
      ),
    ),
  ),
  'notifications' => 
  array (
    'alerts' => 
    array (
      'appointment_alert' => 
      array (
        'name' => 'appointment_alert',
        'description' => 'Notify me on appointments alert',
      ),
      'event_alert' => 
      array (
        'name' => 'event_alert',
        'description' => 'Notify me on events alert',
      ),
      'reminder_alert' => 
      array (
        'name' => 'reminder_alert',
        'description' => 'Notify me on calendar reminders',
      ),
      'task_reminder_alert' => 
      array (
        'name' => 'task_reminder_alert',
        'description' => 'Notify me when a task is expiring',
      ),
      'todo_reminder_alert' => 
      array (
        'name' => 'todo_reminder_alert',
        'description' => 'Notify me when todo task is expiring',
      ),
      'contract_rejected_alert' => 
      array (
        'name' => 'contract_rejected_alert',
        'description' => 'Notify me when a contract is rejected',
      ),
      'contract_signed_alert' => 
      array (
        'name' => 'contract_signed_alert',
        'description' => 'Notify me when a contract is signed',
      ),
      'contract_viewed_alert' => 
      array (
        'name' => 'contract_viewed_alert',
        'description' => 'Notify me when a contract is viewed',
      ),
      'deal_commented' => 
      array (
        'name' => 'deal_commented',
        'description' => 'Notify me when a deal receives a comment',
      ),
      'deal_created' => 
      array (
        'name' => 'deal_created',
        'description' => 'Notify me when a new deal is created',
      ),
      'deal_lost_alert' => 
      array (
        'name' => 'deal_lost_alert',
        'description' => 'Notify me when a deal is lost',
      ),
      'deal_updated_alert' => 
      array (
        'name' => 'deal_updated_alert',
        'description' => 'Notify me when a deal is updated',
      ),
      'deal_won_alert' => 
      array (
        'name' => 'deal_won_alert',
        'description' => 'Notify me when a deal is won',
      ),
      'estimate_viewed_alert' => 
      array (
        'name' => 'estimate_viewed_alert',
        'description' => 'Notify me when an estimate is viewed',
      ),
      'call_alert' => 
      array (
        'name' => 'call_alert',
        'description' => 'Notify me when a scheduled call is due',
      ),
      'invoice_viewed_alert' => 
      array (
        'name' => 'invoice_viewed_alert',
        'description' => 'Notify me when an invoice is viewed by client',
      ),
      'issue_changed_alert' => 
      array (
        'name' => 'issue_changed_alert',
        'description' => 'Notify me when an issue is modified',
      ),
      'issue_commented' => 
      array (
        'name' => 'issue_commented',
        'description' => 'Notify me when an issue receives a new comment',
      ),
      'issue_created_alert' => 
      array (
        'name' => 'issue_created_alert',
        'description' => 'Notify me when a issue is created',
      ),
      'article_commented' => 
      array (
        'name' => 'article_commented',
        'description' => 'Notify me when article receives a new comment',
      ),
      'lead_assigned_alert' => 
      array (
        'name' => 'lead_assigned_alert',
        'description' => 'Notify me when a lead is assigned to me',
      ),
      'lead_commented' => 
      array (
        'name' => 'lead_commented',
        'description' => 'Notify me when lead receives new comment',
      ),
      'lead_converted_alert' => 
      array (
        'name' => 'lead_converted_alert',
        'description' => 'Notify me when a lead is converted to customer',
      ),
      'payment_received_alert' => 
      array (
        'name' => 'payment_received_alert',
        'description' => 'Notify me when a new payment is received',
      ),
      'thank_you_alert' => 
      array (
        'name' => 'thank_you_alert',
        'description' => 'Accept thank you emails after payments',
      ),
      'project_commented' => 
      array (
        'name' => 'project_commented',
        'description' => 'Notify me when project receives a new comment',
      ),
      'task_commented' => 
      array (
        'name' => 'task_commented',
        'description' => 'Notify me when task receives a new comment',
      ),
      'estimate_commented' => 
      array (
        'name' => 'estimate_commented',
        'description' => 'Notify me when estimate receives a new comment',
      ),
      'expense_commented' => 
      array (
        'name' => 'expense_commented',
        'description' => 'Notify me when expense receives a new comment',
      ),
      'invoice_commented' => 
      array (
        'name' => 'invoice_commented',
        'description' => 'Notify me when invoice receives a new comment',
      ),
      'ticket_assigned' => 
      array (
        'name' => 'ticket_assigned',
        'description' => 'Notify me when a ticket is assigned to me',
      ),
      'ticket_closed_alert' => 
      array (
        'name' => 'ticket_closed_alert',
        'description' => 'Notify me when a ticket is closed',
      ),
      'announcement_alert' => 
      array (
        'name' => 'announcement_alert',
        'description' => 'Notify me on announcements alert',
      ),
      'email_opened_alert' => 
      array (
        'name' => 'email_opened_alert',
        'description' => 'Notify me when lead opens email',
      ),
      'estimate_accepted_alert' => 
      array (
        'name' => 'estimate_accepted_alert',
        'description' => 'Notify me when an estimate is approved',
      ),
      'estimate_declined_alert' => 
      array (
        'name' => 'estimate_declined_alert',
        'description' => 'Notify me when an estimate is declined',
      ),
      'ticket_created_alert' => 
      array (
        'name' => 'ticket_created_alert',
        'description' => 'Notify me when a new ticket is created',
      ),
      'ticket_opened_alert' => 
      array (
        'name' => 'ticket_opened_alert',
        'description' => 'Notify me when a ticket is re-opened',
      ),
      'ticket_replied_alert' => 
      array (
        'name' => 'ticket_replied_alert',
        'description' => 'Notify me when a ticket is replied',
      ),
    ),
  ),
  'oauth-5-laravel' => 
  array (
    'storage' => '\\OAuth\\Common\\Storage\\Session',
    'consumers' => 
    array (
      'Facebook' => 
      array (
        'client_id' => '',
        'client_secret' => '',
        'scope' => 
        array (
        ),
      ),
      'Google' => 
      array (
        'client_id' => '',
        'client_secret' => '',
        'scope' => 
        array (
          0 => 'userinfo_email',
          1 => 'userinfo_profile',
          2 => 'https://www.googleapis.com/auth/calendar',
          3 => 'https://mail.google.com/',
          4 => 'https://www.google.com/m8/feeds/',
        ),
      ),
      'Twitter' => 
      array (
        'client_id' => '',
        'client_secret' => '',
      ),
      'Linkedin' => 
      array (
        'client_id' => '',
        'client_secret' => '',
        'scope' => 
        array (
          0 => 'r_basicprofile',
          1 => 'r_network',
          2 => 'w_messages',
        ),
      ),
      'Harvest' => 
      array (
        'client_id' => NULL,
        'client_secret' => NULL,
      ),
      'Box' => 
      array (
        'client_id' => NULL,
        'client_secret' => NULL,
      ),
      'Dropbox' => 
      array (
        'client_id' => NULL,
        'client_secret' => NULL,
      ),
    ),
  ),
  'paypal' => 
  array (
    'mode' => 'live',
    'sandbox' => 
    array (
      'username' => '',
      'password' => '',
      'secret' => '',
      'certificate' => '',
      'app_id' => 'APP-80W284485P519543T',
    ),
    'live' => 
    array (
      'username' => '',
      'password' => '',
      'secret' => '',
      'certificate' => '',
      'app_id' => '',
    ),
    'payment_action' => 'Sale',
    'currency' => 'USD',
    'billing_type' => 'MerchantInitiatedBilling',
    'notify_url' => '',
    'locale' => '',
    'validate_ssl' => true,
  ),
  'pdf' => 
  array (
    'mode' => 'UTF-8',
    'format' => 'A4',
    'default_font_size' => '13',
    'default_font' => 'dejavusanscondensed',
    'custom_font_path' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\resources/fonts/',
    'direction' => 'ltr',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 16,
    'margin_header' => 9,
    'margin_footer' => 9,
    'orientation' => 'P',
    'title' => 'Workice PDF',
    'author' => '',
    'watermark' => 'Workice',
    'show_watermark' => false,
    'watermark_font' => 'dejavusanscondensed',
    'display_mode' => 'fullpage',
    'watermark_text_alpha' => 0.1,
  ),
  'permission' => 
  array (
    'models' => 
    array (
      'permission' => 'Spatie\\Permission\\Models\\Permission',
      'role' => 'Spatie\\Permission\\Models\\Role',
    ),
    'table_names' => 
    array (
      'roles' => 'roles',
      'permissions' => 'permissions',
      'model_has_permissions' => 'model_permissions',
      'model_has_roles' => 'model_has_roles',
      'role_has_permissions' => 'role_permissions',
    ),
    'column_names' => 
    array (
      'model_morph_key' => 'model_id',
    ),
    'display_permission_in_exception' => false,
    'cache' => 
    array (
      'expiration_time' => 
      DateInterval::__set_state(array(
         'y' => 0,
         'm' => 0,
         'd' => 0,
         'h' => 24,
         'i' => 0,
         's' => 0,
         'f' => 0.0,
         'weekday' => 0,
         'weekday_behavior' => 0,
         'first_last_day_of' => 0,
         'invert' => 0,
         'days' => false,
         'special_type' => 0,
         'special_amount' => 0,
         'have_weekday_relative' => 0,
         'have_special_relative' => 0,
      )),
      'key' => 'spatie.permission.cache',
      'model_key' => 'name',
      'store' => 'default',
    ),
  ),
  'queue' => 
  array (
    'default' => 'database',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => 'your-public-key',
        'secret' => 'your-secret-key',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'your-queue-name',
        'region' => 'us-east-1',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
      ),
    ),
    'failed' => 
    array (
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'reauthenticate' => 
  array (
    'timeout' => 3600,
    'reset' => true,
    'route' => 'users.reauthenticate',
  ),
  'secure-headers' => 
  array (
    'server' => '',
    'x-content-type-options' => 'nosniff',
    'x-download-options' => 'noopen',
    'x-frame-options' => 'sameorigin',
    'x-permitted-cross-domain-policies' => 'none',
    'x-power-by' => '',
    'x-xss-protection' => '1; mode=block',
    'referrer-policy' => 'no-referrer-when-downgrade',
    'clear-site-data' => 
    array (
      'enable' => false,
      'all' => false,
      'cache' => true,
      'cookies' => true,
      'storage' => true,
      'executionContexts' => true,
    ),
    'hsts' => 
    array (
      'enable' => true,
      'max-age' => 31536000,
      'include-sub-domains' => true,
    ),
    'expect-ct' => 
    array (
      'enable' => false,
      'max-age' => 2147483648,
      'enforce' => false,
      'report-uri' => NULL,
    ),
    'hpkp' => 
    array (
      'hashes' => 
      array (
      ),
      'include-sub-domains' => false,
      'max-age' => 15552000,
      'report-only' => false,
      'report-uri' => NULL,
    ),
    'feature-policy' => 
    array (
      'enable' => true,
      'accelerometer' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'ambient-light-sensor' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'autoplay' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'camera' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'display-capture' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'document-domain' => 
      array (
        'none' => false,
        '*' => true,
        'self' => false,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'encrypted-media' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'fullscreen' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'geolocation' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'gyroscope' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'magnetometer' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'microphone' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'midi' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'payment' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'picture-in-picture' => 
      array (
        'none' => false,
        '*' => true,
        'self' => false,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'speaker' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'sync-xhr' => 
      array (
        'none' => false,
        '*' => true,
        'self' => false,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'usb' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
      'vr' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'src' => false,
        'allow' => 
        array (
        ),
      ),
    ),
    'custom-csp' => NULL,
    'csp' => 
    array (
      'report-only' => false,
      'report-uri' => 'https://your-report-uri.com',
      'upgrade-insecure-requests' => false,
      'https-transform-on-https-connections' => true,
      'base-uri' => 
      array (
      ),
      'default-src' => 
      array (
        'self' => true,
      ),
      'child-src' => 
      array (
      ),
      'worker-src' => 
      array (
        'allow' => 
        array (
          0 => 'blob:',
        ),
      ),
      'frame-src' => 
      array (
        'allow' => 
        array (
          0 => 'https://*.stripe.com',
          1 => 'https://*.twitter.com',
          2 => 'https://onesignal.com',
          3 => 'https://*.paypal.com',
          4 => 'https://*.razorpay.com',
          5 => 'https://*.braintreegateway.com',
          6 => 'https://*.driftt.com',
          7 => 'https://va.tawk.to',
          8 => 'https://*.google.com',
        ),
      ),
      'script-src' => 
      array (
        'allow' => 
        array (
          0 => 'https://*.googleapis.com',
          1 => 'https://code.jquery.com',
          2 => 'https://www.googletagmanager.com',
          3 => 'https://www.google-analytics.com',
          4 => 'https://*.pusher.com',
          5 => 'https://cdnjs.cloudflare.com',
          6 => 'http://cdnjs.cloudflare.com',
          7 => 'https://www.gstatic.com',
          8 => 'https://cdn.jsdelivr.net',
          9 => 'https://static.filestackapi.com',
          10 => 'https://unpkg.com',
          11 => 'https://*.stripe.com',
          12 => 'https://use.fontawesome.com/',
          13 => 'https://*.newrelic.com',
          14 => 'https://bam.nr-data.net',
          15 => 'https://*.crisp.chat',
          16 => 'https://cdn.datatables.net',
          17 => 'https://platform.twitter.com',
          18 => 'https://*.onesignal.com',
          19 => 'https://onesignal.com',
          20 => 'https://*.paypalobjects.com',
          21 => 'https://*.paypal.com',
          22 => 'https://*.2checkout.com',
          23 => 'https://*.razorpay.com',
          24 => 'https://*.braintreegateway.com',
          25 => 'https://*.driftt.com',
          26 => 'https://embed.tawk.to',
          27 => 'https://*.google.com',
        ),
        'hashes' => 
        array (
        ),
        'nonces' => 
        array (
        ),
        'unsafe-inline' => true,
        'unsafe-eval' => true,
        'self' => true,
      ),
      'style-src' => 
      array (
        'allow' => 
        array (
          0 => 'https: //fonts.googleapis.com',
          1 => 'http: //fonts.googleapis.com',
          2 => 'https://maxcdn.bootstrapcdn.com',
          3 => 'https://www.gstatic.com',
          4 => 'https://cdn.datatables.net',
          5 => 'https://cdn.jsdelivr.net/',
          6 => 'https://static.filestackapi.com',
          7 => 'https://*.crisp.chat',
          8 => 'https://onesignal.com',
          9 => 'https://*.stripe.com',
          10 => 'https://*.braintreegateway.com',
        ),
        'self' => true,
        'unsafe-inline' => true,
      ),
      'img-src' => 
      array (
        'allow' => 
        array (
          0 => '*',
          1 => 'data:',
        ),
        'types' => 
        array (
        ),
        'self' => true,
        'data' => true,
      ),
      'font-src' => 
      array (
        'allow' => 
        array (
          0 => 'https://fonts.gstatic.com',
          1 => 'http://fonts.gstatic.com',
          2 => 'https://maxcdn.bootstrapcdn.com',
          3 => 'https://*.crisp.chat',
          4 => 'https://static-v.tawk.to',
          5 => 'data:',
        ),
        'data' => 'fonts.gstatic.com',
        'self' => true,
      ),
      'connect-src' => 
      array (
        'allow' => 
        array (
          0 => 'https://*.pusher.com',
          1 => 'wss://*.pusher.com',
          2 => 'wss://*.pusherapp.com',
          3 => 'wss://*.relay.crisp.chat',
          4 => 'https://*.crisp.chat',
          5 => 'https://*.filestackapi.com',
          6 => 'https://s3.amazonaws.com',
          7 => 'https://*.gitbench.com',
          8 => 'https://*.stripe.com',
          9 => 'https://*.workice.com',
          10 => 'https://*.paypal.com',
          11 => 'https://*.braintree-api.com',
          12 => 'https://*.braintreegateway.com',
          13 => 'https://*.google-analytics.com',
          14 => 'https://*.tawk.to',
          15 => 'wss://*.tawk.to',
        ),
        'self' => true,
      ),
      'form-action' => 
      array (
        'allow' => 
        array (
          0 => 'https://*.twitter.com',
          1 => 'https://*.paypal.com',
          2 => 'https://*.mollie.com',
          3 => 'https://va.tawk.to',
        ),
        'self' => true,
      ),
      'frame-ancestors' => 
      array (
      ),
      'media-src' => 
      array (
      ),
      'object-src' => 
      array (
      ),
      'plugin-types' => 
      array (
      ),
    ),
  ),
  'self-diagnosis' => 
  array (
    'environment_aliases' => 
    array (
      'prod' => 'production',
      'live' => 'production',
      'local' => 'development',
    ),
    'checks' => 
    array (
      0 => 'BeyondCode\\SelfDiagnosis\\Checks\\AppKeyIsSet',
      1 => 'BeyondCode\\SelfDiagnosis\\Checks\\CorrectPhpVersionIsInstalled',
      'BeyondCode\\SelfDiagnosis\\Checks\\DatabaseCanBeAccessed' => 
      array (
        'default_connection' => true,
        'connections' => 
        array (
        ),
      ),
      'BeyondCode\\SelfDiagnosis\\Checks\\DirectoriesHaveCorrectPermissions' => 
      array (
        'directories' => 
        array (
          0 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage',
          1 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\bootstrap/cache',
        ),
      ),
      2 => 'BeyondCode\\SelfDiagnosis\\Checks\\EnvFileExists',
      3 => 'BeyondCode\\SelfDiagnosis\\Checks\\ExampleEnvironmentVariablesAreSet',
      'BeyondCode\\SelfDiagnosis\\Checks\\LocalesAreInstalled' => 
      array (
        'required_locales' => 
        array (
          0 => 'en_US',
          1 => 'en_US.utf8',
        ),
      ),
      4 => 'BeyondCode\\SelfDiagnosis\\Checks\\MaintenanceModeNotEnabled',
      5 => 'BeyondCode\\SelfDiagnosis\\Checks\\MigrationsAreUpToDate',
      'BeyondCode\\SelfDiagnosis\\Checks\\PhpExtensionsAreInstalled' => 
      array (
        'extensions' => 
        array (
          0 => 'openssl',
          1 => 'PDO',
          2 => 'mbstring',
          3 => 'tokenizer',
          4 => 'xml',
          5 => 'ctype',
          6 => 'json',
        ),
        'include_composer_extensions' => true,
      ),
      6 => 'BeyondCode\\SelfDiagnosis\\Checks\\StorageDirectoryIsLinked',
    ),
    'environment_checks' => 
    array (
      'development' => 
      array (
        0 => 'BeyondCode\\SelfDiagnosis\\Checks\\ComposerWithDevDependenciesIsUpToDate',
        1 => 'BeyondCode\\SelfDiagnosis\\Checks\\ConfigurationIsNotCached',
        2 => 'BeyondCode\\SelfDiagnosis\\Checks\\RoutesAreNotCached',
        3 => 'BeyondCode\\SelfDiagnosis\\Checks\\ExampleEnvironmentVariablesAreUpToDate',
      ),
      'production' => 
      array (
        0 => 'BeyondCode\\SelfDiagnosis\\Checks\\ComposerWithoutDevDependenciesIsUpToDate',
        1 => 'BeyondCode\\SelfDiagnosis\\Checks\\ConfigurationIsCached',
        2 => 'BeyondCode\\SelfDiagnosis\\Checks\\DebugModeIsNotEnabled',
        'BeyondCode\\SelfDiagnosis\\Checks\\PhpExtensionsAreDisabled' => 
        array (
          'extensions' => 
          array (
            0 => 'xdebug',
          ),
        ),
        3 => 'BeyondCode\\SelfDiagnosis\\Checks\\RoutesAreCached',
      ),
    ),
  ),
  'sentry' => 
  array (
    'dsn' => '',
    'environment' => NULL,
    'breadcrumbs' => 
    array (
      'logs' => true,
      'sql_queries' => true,
      'sql_bindings' => true,
      'queue_info' => true,
      'command_info' => true,
    ),
    'send_default_pii' => false,
    'breadcrumbs.sql_bindings' => true,
  ),
  'services' => 
  array (
    'mailgun' => 
    array (
      'domain' => '',
      'secret' => '',
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'sparkpost' => 
    array (
      'secret' => '',
    ),
    'nexmo' => 
    array (
      'key' => '',
      'secret' => '',
      'sms_from' => '15556666666',
      'active' => false,
    ),
    'twitter' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => '/callback/twitter',
    ),
    'github' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => '/callback/github',
    ),
    'facebook' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => '/callback/facebook',
    ),
    'google' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => '/callback/google',
    ),
    'gitlab' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => '/callback/gitlab',
    ),
    'linkedin' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => '/callback/linkedin',
    ),
    'stripe' => 
    array (
      'model' => 'Modules\\Clients\\Entities\\Client',
      'key' => '',
      'secret' => '',
      'webhook' => 
      array (
        'secret' => '',
        'tolerance' => 300,
      ),
    ),
    'razorpay' => 
    array (
      'keyId' => '',
      'secretKey' => '',
    ),
    '2checkout' => 
    array (
      'sellerId' => '',
      'publishableKey' => '',
      'privateKey' => '',
    ),
    'braintree' => 
    array (
      'merchantId' => '',
      'publicKey' => '',
      'privateKey' => '',
    ),
    'wepay' => 
    array (
      'accountId' => '',
      'clientId' => '',
      'secretId' => '',
      'accessToken' => '',
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => '120',
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'threat_intelligent_center_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => false,
    'http_only' => true,
    'same_site' => NULL,
  ),
  'system' => 
  array (
    'track_emails' => true,
    'avatar_dir' => 'public/avatars',
    'signature_dir' => 'public/signatures',
    'logos_dir' => 'public/logos',
    'site_dir' => 'public/site',
    'media_dir' => 'public/media',
    'pdf_font' => 'lato',
    'pdf' => 
    array (
      'invoices' => 
      array (
        'template' => 'default',
      ),
    ),
    'secure_password' => false,
    'imap_enabled' => true,
    'asset_cdn' => false,
    'daily_digest' => 
    array (
      'enabled' => true,
      'send_at' => '23:58',
    ),
    'cashier' => 
    array (
      'currency' => 'USD',
      'symbol' => '$',
    ),
    'activity_days' => '30',
    'task_due_after' => 7,
    'budget_exceeds' => 90,
    'remind_overdue_tasks' => true,
    'show_items_invoice_mail' => false,
    'show_items_estimate_mail' => false,
    'logo_on_emails' => false,
    'supported_locales' => 
    array (
      0 => 'en',
      1 => 'fr',
      2 => 'de',
      3 => 'es',
    ),
    'alert_todo_before' => '2',
    'autoremind_contracts' => true,
    'remind_contracts_before' => 2,
    'csv_max_file' => 500000,
    'supported_currency_words' => 
    array (
      0 => 'ALL',
      1 => 'AUD',
      2 => 'BAM',
      3 => 'BGN',
      4 => 'BRL',
      5 => 'BYR',
      6 => 'CAD',
      7 => 'CHF',
      8 => 'CYP',
      9 => 'CZK',
      10 => 'DKK',
      11 => 'EEK',
      12 => 'EUR',
      13 => 'GBP',
      14 => 'HKD',
      15 => 'HRK',
      16 => 'HUF',
      17 => 'ILS',
      18 => 'ISK',
      19 => 'JPY',
      20 => 'LTL',
      21 => 'LVL',
      22 => 'MKD',
      23 => 'MTL',
      24 => 'NOK',
      25 => 'PLN',
      26 => 'ROL',
      27 => 'RUB',
      28 => 'SEK',
      29 => 'SIT',
      30 => 'SKK',
      31 => 'TRL',
      32 => 'UAH',
      33 => 'USD',
      34 => 'YUM',
      35 => 'ZAR',
    ),
    'telegram_bot_token' => '',
    'stripe_telegram_token' => '',
    'pusher_enabled' => false,
    'drift_enabled' => false,
    'crisp_enabled' => false,
    'enable_onesignal' => false,
    'enable_tawk' => false,
    'material_design' => false,
    'contract_color' => '#3869D4',
    'saleurl' => 'https://workice.com',
    'error_report_uri' => 'https://desk.workice.com/api/v1/errors',
    'support_uri' => 'https://desk.workice.com/api/v1/public-ticket',
  ),
  'taggable' => 
  array (
    'delimiters' => ',;',
    'glue' => ',',
    'normalizer' => 'mb_strtolower',
    'connection' => NULL,
    'throwEmptyExceptions' => false,
    'taggedModels' => 
    array (
    ),
    'model' => 'App\\Entities\\Tag',
  ),
  'talk' => 
  array (
    'user' => 
    array (
      'model' => '\\Modules\\Users\\Entities\\User',
    ),
    'broadcast' => 
    array (
      'enable' => true,
      'app_name' => 'workice',
      'pusher' => 
      array (
        'app_id' => '',
        'app_key' => '',
        'app_secret' => '',
        'options' => 
        array (
          'cluster' => 'ap2',
          'encrypted' => true,
        ),
      ),
    ),
  ),
  'telescope' => 
  array (
    'path' => 'telescope',
    'driver' => 'database',
    'enabled' => true,
    'storage' => 
    array (
      'database' => 
      array (
        'connection' => 'mysql',
      ),
    ),
    'limit' => 100,
    'middleware' => 
    array (
      0 => 'web',
      1 => 'Laravel\\Telescope\\Http\\Middleware\\Authorize',
    ),
    'watchers' => 
    array (
      'Laravel\\Telescope\\Watchers\\CacheWatcher' => true,
      'Laravel\\Telescope\\Watchers\\CommandWatcher' => true,
      'Laravel\\Telescope\\Watchers\\DumpWatcher' => true,
      'Laravel\\Telescope\\Watchers\\EventWatcher' => true,
      'Laravel\\Telescope\\Watchers\\ExceptionWatcher' => true,
      'Laravel\\Telescope\\Watchers\\JobWatcher' => true,
      'Laravel\\Telescope\\Watchers\\LogWatcher' => true,
      'Laravel\\Telescope\\Watchers\\MailWatcher' => true,
      'Laravel\\Telescope\\Watchers\\ModelWatcher' => true,
      'Laravel\\Telescope\\Watchers\\NotificationWatcher' => true,
      'Laravel\\Telescope\\Watchers\\QueryWatcher' => 
      array (
        'enabled' => true,
        'slow' => 100,
      ),
      'Laravel\\Telescope\\Watchers\\RedisWatcher' => true,
      'Laravel\\Telescope\\Watchers\\RequestWatcher' => true,
      'Laravel\\Telescope\\Watchers\\ScheduleWatcher' => true,
    ),
  ),
  'toastr' => 
  array (
    'options' => 
    array (
      'closeButton' => true,
      'debug' => false,
      'newestOnTop' => false,
      'progressBar' => false,
      'positionClass' => 'toast-top-right',
      'preventDuplicates' => false,
      'onclick' => NULL,
      'showDuration' => '300',
      'hideDuration' => '2000',
      'timeOut' => '5000',
      'extendedTimeOut' => '1000',
      'showEasing' => 'swing',
      'hideEasing' => 'linear',
      'showMethod' => 'fadeIn',
      'hideMethod' => 'fadeOut',
      'rtl' => false,
    ),
  ),
  'translation-loader' => 
  array (
    'translation_loaders' => 
    array (
      0 => 'Spatie\\TranslationLoader\\TranslationLoaders\\Db',
    ),
    'model' => 'Spatie\\TranslationLoader\\LanguageLine',
    'translation_manager' => 'Spatie\\TranslationLoader\\TranslationLoaderManager',
  ),
  'trustedproxy' => 
  array (
    'proxies' => NULL,
    'headers' => 30,
  ),
  'updater' => 
  array (
    'tmp_path' => 'app/updates/tmp',
    'update_baseurl' => 'https://desk.workice.com/updates/latest',
    'middleware' => 
    array (
      0 => 'web',
      1 => 'auth',
    ),
    'exclude_folders' => 
    array (
      0 => 'node_modules',
      1 => 'bootstrap/cache',
      2 => 'bower',
      3 => 'storage/app/uploads',
      4 => 'storage/app/public',
      5 => 'storage/app/tmp',
      6 => 'storage/framework',
      7 => 'storage/logs',
      8 => 'storage/updates',
      9 => 'vendor',
    ),
    'artisan_commands' => 
    array (
      'pre_update' => 
      array (
        'updater:prepare' => 
        array (
          'class' => 'App\\Console\\Commands\\PreUpdateTasks',
          'params' => 
          array (
          ),
        ),
      ),
      'post_update' => 
      array (
        'postupdate:cleanup' => 
        array (
          'class' => 'App\\Console\\Commands\\PostUpdateCleanup',
          'params' => 
          array (
          ),
        ),
      ),
    ),
    'allow_users_id' => 
    array (
      0 => 1,
    ),
  ),
  'user-verification' => 
  array (
    'email' => 
    array (
      'type' => 'markdown',
      'view' => NULL,
    ),
    'auto-login' => false,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'C:\\xampp\\htdocs\\threat-intelligent-center\\resources\\views',
    ),
    'compiled' => 'C:\\xampp\\htdocs\\threat-intelligent-center\\storage\\framework\\views',
  ),
  'passport' => 
  array (
    'private_key' => NULL,
    'public_key' => NULL,
  ),
  'activity' => 
  array (
    'name' => 'Activity',
  ),
  'settings' => 
  array (
    'name' => 'Settings',
  ),
  'messages' => 
  array (
    'name' => 'Messages',
  ),
  'migration' => 
  array (
    'name' => 'Migration',
  ),
  'milestones' => 
  array (
    'name' => 'Milestones',
  ),
  'monitoring' => 
  array (
    'name' => 'Monitoring',
  ),
  'monitoringcompromised' => 
  array (
    'name' => 'MonitoringCompromised',
  ),
  'monitoringvulnerabilitys' => 
  array (
    'name' => 'MonitoringVulnerabilitys',
  ),
  'news' => 
  array (
    'name' => 'News',
  ),
  'notes' => 
  array (
    'name' => 'Notes',
  ),
  'payments' => 
  array (
    'name' => 'Payments',
  ),
  'projects' => 
  array (
    'name' => 'Projects',
    'billing_methods' => 
    array (
      0 => 'hourly_task_rate',
      1 => 'hourly_staff_rate',
      2 => 'hourly_project_rate',
      3 => 'fixed_rate',
    ),
  ),
  'rssfeedsettings' => 
  array (
    'name' => 'RSSFeedSettings',
  ),
  'scans' => 
  array (
    'name' => 'Scans',
  ),
  'sitesettings' => 
  array (
    'name' => 'SiteSettings',
  ),
  'leads' => 
  array (
    'name' => 'Leads',
  ),
  'social' => 
  array (
    'name' => 'Social',
  ),
  'subscriptions' => 
  array (
    'name' => 'Subscriptions',
  ),
  'tasks' => 
  array (
    'name' => 'Tasks',
  ),
  'teams' => 
  array (
    'name' => 'Teams',
  ),
  'tickets' => 
  array (
    'name' => 'Tickets',
  ),
  'timetracking' => 
  array (
    'name' => 'Timetracking',
  ),
  'todos' => 
  array (
    'name' => 'Todos',
  ),
  'updatecode' => 
  array (
    'name' => 'UpdateCode',
  ),
  'updates' => 
  array (
    'name' => 'Updates',
  ),
  'users' => 
  array (
    'name' => 'Users',
  ),
  'vmclientsettings' => 
  array (
    'name' => 'VMClientSettings',
  ),
  'vulnerabilitysettings' => 
  array (
    'name' => 'VulnerabilitySettings',
  ),
  'webdefacement' => 
  array (
    'name' => 'WebDefacement',
  ),
  'manageassets' => 
  array (
    'name' => 'ManageAssets',
  ),
  'knowledgebase' => 
  array (
    'name' => 'Knowledgebase',
  ),
  'alert' => 
  array (
    'name' => 'Alert',
  ),
  'contacts' => 
  array (
    'name' => 'Contacts',
  ),
  'analytics' => 
  array (
    'name' => 'Analytics',
  ),
  'apiindicators' => 
  array (
    'name' => 'ApiIndicators',
  ),
  'apiintegration' => 
  array (
    'name' => 'ApiIntegration',
  ),
  'assetsettingcompromised' => 
  array (
    'name' => 'AssetSettingCompromised',
  ),
  'assetsettingvulnerabilitys' => 
  array (
    'name' => 'AssetSettingVulnerabilitys',
  ),
  'assets' => 
  array (
    'name' => 'Assets',
  ),
  'blog' => 
  array (
    'name' => 'Blog',
  ),
  'calendar' => 
  array (
    'name' => 'Calendar',
  ),
  'categorysettings' => 
  array (
    'name' => 'CategorySettings',
  ),
  'clients' => 
  array (
    'name' => 'Clients',
  ),
  'comments' => 
  array (
    'name' => 'Comments',
  ),
  'compromised' => 
  array (
    'name' => 'Compromised',
  ),
  'contracts' => 
  array (
    'name' => 'Contracts',
  ),
  'items' => 
  array (
    'name' => 'Items',
  ),
  'creditnotes' => 
  array (
    'name' => 'Creditnotes',
  ),
  'darkweb' => 
  array (
    'name' => 'DarkWeb',
  ),
  'dashboard' => 
  array (
    'name' => 'Dashboard',
  ),
  'dashboardnew' => 
  array (
    'name' => 'DashboardNew',
  ),
  'deals' => 
  array (
    'name' => 'Deals',
  ),
  'estimates' => 
  array (
    'name' => 'Estimates',
  ),
  'expenses' => 
  array (
    'name' => 'Expenses',
  ),
  'extras' => 
  array (
    'name' => 'Extras',
  ),
  'files' => 
  array (
    'name' => 'Files',
  ),
  'indicators' => 
  array (
    'name' => 'Indicators',
  ),
  'invoices' => 
  array (
    'name' => 'Invoices',
  ),
  'issues' => 
  array (
    'name' => 'Issues',
  ),
  'webhook' => 
  array (
    'name' => 'Webhook',
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
