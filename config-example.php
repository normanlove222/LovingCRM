<?php
// config.php is your connction settings for all your needed environments.
//USE YOUR OWN INFO     
return [
    'development' => [
        'dsn' => 'mysql:host=localhost;dbname=lovingcrm',
        'username' => 'root',
        'password' => '',
        'port' => '3306',
        'charset' => 'utf8mb4',
        'DEV_LOG_PATH' => 'D:wamp64\www\lovingcrm\PHP_errors.log',
    ],
    'production' => [
        'dsn' => 'mysql:host=mysql.YOUR-DOMAIN.com;dbname=YOUR-DOMAIN',
        'username' => 'USERNAME',
        'password' => 'YOUR-DOMAIN',
        'port' => '3306',
        'charset' => 'utf8mb4',
        // 'DEV_LOG_PATH' => 'D:wamp64\www\YOUR-DOMAIN\PHP_errors.log',
    ],
    'testing' => [
        'dsn' => 'mysql:host=localhost;dbname=YOUR-DOMAIN_test',
        'username' => 'root',
        'password' => '',
        'port' => '3306',
        'charset' => 'utf8mb4',
        'DEV_LOG_PATH' => 'D:wamp64\www\YOUR-DOMAIN\PHP_errors.log',
    ],
    'demo' => [
        'dsn' => 'mysql:host=mysql.YOUR-DOMAIN.com;dbname=YOUR-DOMAIN',
        'username' => 'USERNAME',
        'password' => 'YOUR-PASSWORD',
        'port' => '3306',
        'charset' => 'utf8mb4',
        // 'DEV_LOG_PATH' => 'D:wamp64\www\YOUR-DOMAIN\PHP_errors.log',
    ],
];
