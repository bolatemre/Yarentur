<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');
date_default_timezone_set('Europe/Istanbul');
mb_internal_encoding('UTF-8');

// İsteğe bağlı yerel ayarlar (git'e girmez): includes/config.local.php
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_name('yarentur_sid');
    session_start();
}

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
