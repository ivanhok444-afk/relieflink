<?php
/**
 * System Configuration
 * ReliefLink System Configuration
 */

// Application Settings
define('APP_NAME', 'ReliefLink');
define('APP_VERSION', '1.0.0');
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$applicationRoot = realpath(__DIR__ . '/..');
$appBasePath = '';
if ($documentRoot !== false && $applicationRoot !== false) {
    $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
    $applicationRoot = rtrim(str_replace('\\', '/', $applicationRoot), '/');
    if ($applicationRoot !== $documentRoot) {
        $documentRootPrefix = $documentRoot . '/';
        $rootPrefixMatches = PHP_OS_FAMILY === 'Windows'
            ? strncasecmp($applicationRoot, $documentRootPrefix, strlen($documentRootPrefix)) === 0
            : strncmp($applicationRoot, $documentRootPrefix, strlen($documentRootPrefix)) === 0;
        if ($rootPrefixMatches) {
            $appBasePath = '/' . trim(substr($applicationRoot, strlen($documentRootPrefix)), '/');
        }
    }
}
define('APP_BASE_PATH', $appBasePath);
define('APP_URL', rtrim(getenv('APP_URL') ?: 'http://localhost' . APP_BASE_PATH, '/'));

// Database Configuration
$appEnvironment = strtolower((string) getenv('AIVAN_ENV'));
if ($appEnvironment === 'production') {
    foreach (['AIVAN_DB_HOST', 'AIVAN_DB_NAME', 'AIVAN_DB_USER', 'AIVAN_DB_PASS'] as $databaseVariable) {
        $databaseValue = getenv($databaseVariable);
        if ($databaseValue === false || $databaseValue === '') {
            throw new RuntimeException('Production database environment variables are not fully configured.');
        }
    }
}

define('DB_HOST', getenv('AIVAN_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('AIVAN_DB_NAME') ?: 'relief_goods_db');
define('DB_USER', getenv('AIVAN_DB_USER') ?: 'root');
$databasePassword = getenv('AIVAN_DB_PASS');
define('DB_PASS', $databasePassword === false ? '' : $databasePassword);

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_COOKIE_LIFETIME', 3600);

// System Settings
define('ITEMS_PER_PAGE', 10);
define('MAX_GOODS_PER_REQUEST', 10);
define('LOW_STOCK_THRESHOLD', 10);
define('REQUEST_REQUIRES_APPROVAL', true);

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// Email Settings (if needed)
define('SYSTEM_EMAIL', 'admin@reliefgoods.com');
define('SYSTEM_EMAIL_NAME', 'Relief Goods System');
define('SYSTEM_PHONE', '+63-XXX-XXXX-XXX');

// Error Logging
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/logs/system.log');

// Security Settings
define('PASSWORD_MIN_LENGTH', 12);
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_REGENERATE_ID', true);

// Date/Time Settings
define('DEFAULT_TIMEZONE', 'Asia/Manila');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Pagination Settings
define('ROWS_PER_PAGE', 15);
define('MAX_DISPLAY_PAGES', 10);

// Application Status
define('APP_STATUS', 'active'); // active, maintenance, closed

// Features (Can be toggled)
define('ENABLE_QR_CODE', true);
define('ENABLE_SMS_NOTIFICATION', false);
define('ENABLE_EMAIL_NOTIFICATION', true);
define('ENABLE_ACTIVITY_LOG', true);

// Report Settings
define('REPORT_DATE_FORMAT', 'Y-m-d');
define('REPORT_CURRENCY', '₱');
define('REPORT_CURRENCY_CODE', 'PHP');

// Backup Settings
define('AUTO_BACKUP_ENABLED', true);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly

?>
