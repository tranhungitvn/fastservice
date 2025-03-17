<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fastservice');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'FastService');
define('SITE_URL', 'http://localhost/fastservice');

// File upload configuration
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/fastservice/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Session configuration
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7); // 1 week
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7); // 1 week

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');