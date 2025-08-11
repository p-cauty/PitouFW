<?php

require_once __DIR__ . '/config/config.php';

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'migration',
            'default_environment' => 'local',
            'local' => [
                'adapter' => 'mysql',
                'host' => DB_HOST,
                'name' => DB_NAME,
                'user' => DB_USER,
                'pass' => DB_PASS,
                'port' => '3306',
                'charset' => 'utf8mb4',
            ]
        ],
        'version_order' => 'creation'
    ];
