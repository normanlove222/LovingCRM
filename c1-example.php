<?php

//establish the server type; testing, development, production etc.
// define('ENVIRONMENT', 'development');
// define('ENVIRONMENT', 'demo');
// define('ENVIRONMENT', 'testing');
define('ENVIRONMENT', 'production');

date_default_timezone_set('US/Pacific');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');
ini_set('error_log', dirname(__FILE__) . '/error.log');

// Increase the maximum post size
ini_set('post_max_size', '1024M');

// Increase the maximum execution time to 5 minutes (300 seconds)
ini_set('max_execution_time', 300);

ini_set('memory_limit', '1024M');

$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // PDO::ATTR_EMULATE_PREPARES   => false,
];

//constants used in app
define("SITE_DOMAIN_NAME", "yourdomain.com");
define('SITE_EMAIL', 'love@yourdomain.com');
define('SEND_EMAIL', 'love@yourdomain.com');
define('EMAIL_NAME', 'Your Domain');
define('SUPPORT_EMAIL', 'support@yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('ADMIN_NAME', 'Admin');

// //SendGrid constants used for all Production live mail functions
define('SG_API_KEY', 'SG.yourkey');
define('CAPTCHA', 'yourcaptcha'); //enter you chosen capture string like jW78dgh. 

$config = include 'config.php';
$environment = ENVIRONMENT;

$dsn = $config[$environment]['dsn'];
$user = $config[$environment]['username'];
$pass = $config[$environment]['password'];


try {
    $pdo = new PDO($dsn, $user, $pass, $opt);
    // Set the timezone for the MySQL session
    $pdo->exec("SET time_zone = '-07:00'");  //PDT
} catch (PDOException $e) {
    die('Database connection failed for your app name: ' . $e->getMessage());
}
