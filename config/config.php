<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'academic_cash_db');

// Exchange Rate API (Example using a placeholder, replace with real key if available)
define('EXCHANGE_RATE_API_KEY', 'YOUR_API_KEY');
define('BASE_CURRENCY', 'IDR');

// Connect to Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper Function: Get Exchange Rate
function getExchangeRate($from, $to)
{
    if ($from === $to)
        return 1;

    // In a real scenario, cache this or fetch from DB to avoid API limits
    // For demo, we might mock it or use a free endpoint
    // $url = "https://api.exchangerate-api.com/v4/latest/$from";

    // Mocking for now to ensure stability without API key
    $rates = [
        'IDR' => 1,
        'USD' => 0.000065, // Example: 1 IDR = 0.000065 USD
        'EUR' => 0.000060,
        'SGD' => 0.000088
    ];

    if (isset($rates[$to])) {
        return $rates[$to];
    }

    return 1;
}

// Helper: Format Currency
function formatCurrency($amount, $currency = 'IDR')
{
    if ($currency === 'IDR') {
        return "Rp " . number_format($amount, 0, ',', '.');
    }
    return "$currency " . number_format($amount, 2);
}
?>