<?php
// Include vendor autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Autoloader for classes
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../Services/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load .env file if exists
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Initialize core objects
$db = Database::getInstance();
$auth = new Auth();
$analytics = new AnalyticsService();
$notifications = new NotificationService();
$apiClient = new ApiClient();
$currencyService = new CurrencyService();

// Helper function for currency formatting
function formatCurrency($amount, $currencyCode)
{
    return $currencyCode . ' ' . number_format($amount, 2);
}
