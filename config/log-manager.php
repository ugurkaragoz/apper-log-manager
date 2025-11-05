<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Log Manager Enabled
    |--------------------------------------------------------------------------
    |
    | Log sistemini global olarak aktif/deaktif etmek için kullanılır.
    |
    */
    'enabled' => env('LOG_MANAGER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | Logların kaydedileceği veritabanı tablo adı.
    |
    */
    'table' => env('LOG_MANAGER_TABLE', 'logs'),

    /*
    |--------------------------------------------------------------------------
    | Tool Name
    |--------------------------------------------------------------------------
    |
    | Hangi uygulama/tool'dan log geldiğini belirtir.
    |
    */
    'tool_name' => env('LOG_MANAGER_TOOL_NAME', env('APP_NAME', 'Laravel')),

    /*
    |--------------------------------------------------------------------------
    | Silent Failures
    |--------------------------------------------------------------------------
    |
    | Log kaydı sırasında oluşan hataları sessizce yoksay.
    | false olursa hatalar exception olarak fırlatılır.
    |
    */
    'silent_failures' => env('LOG_MANAGER_SILENT_FAILURES', true),

    /*
    |--------------------------------------------------------------------------
    | Auto Log CRUD Operations
    |--------------------------------------------------------------------------
    |
    | Loggable trait'i kullanılan modellerde CRUD işlemlerini otomatik logla.
    |
    */
    'auto_log_crud' => env('LOG_MANAGER_AUTO_LOG_CRUD', true),

    /*
    |--------------------------------------------------------------------------
    | Excluded Attributes
    |--------------------------------------------------------------------------
    |
    | Tüm modellerde default olarak loglanmayacak alanlar.
    |
    */
    'excluded_attributes' => [
        'password',
        'remember_token',
        'api_token',
        'created_at',
        'updated_at',
    ],
];
