<?php

// Default config
$config = [
    'pdo' => [
        'dsn' => 'pgsql:dbname=hierall;host=localhost;user=hierall;password=hierall',
    ]
];

// OpenShift config replace with ENV variables
if (getenv('OPENSHIFT_POSTGRESQL_DB_HOST') !== false) {
    $config['pdo']['dsn'] = 'pgsql:' . join(';', [
            'dbname=hierall',
            'host=' . getenv('OPENSHIFT_POSTGRESQL_DB_HOST'),
            'port=' . getenv('OPENSHIFT_POSTGRESQL_DB_PORT'),
            'user=' . getenv('OPENSHIFT_POSTGRESQL_DB_USERNAME'),
            'password=' . getenv('OPENSHIFT_POSTGRESQL_DB_PASSWORD')
        ]);
}

return $config;
