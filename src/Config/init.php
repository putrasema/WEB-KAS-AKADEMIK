<?php

require_once __DIR__ . '/../../vendor/autoload.php';


spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../Services/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});


if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '='))
            continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);


        if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
            $value = $matches[2];
        }

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}


$db = Database::getInstance();
$auth = new Auth();
$analytics = new AnalyticsService();
$notifications = new NotificationService();
$apiClient = new ApiClient();
$currencyService = new CurrencyService();


function formatCurrency($amount, $currencyCode)
{
    return $currencyCode . ' ' . number_format($amount, 2);
}
