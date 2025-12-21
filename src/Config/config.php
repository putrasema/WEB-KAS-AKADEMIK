<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'academic_cash_db');


define('EXCHANGE_RATE_API_KEY', 'YOUR_API_KEY');
define('BASE_CURRENCY', 'IDR');


$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function getExchangeRate($from, $to)
{
    if ($from === $to)
        return 1;




    $rates = [
        'IDR' => 1,
        'USD' => 0.000065,
        'EUR' => 0.000060,
        'SGD' => 0.000088
    ];

    if (isset($rates[$to])) {
        return $rates[$to];
    }

    return 1;
}


function formatCurrency($amount, $currency = 'IDR')
{
    if ($currency === 'IDR') {
        return "Rp " . number_format($amount, 0, ',', '.');
    }
    return "$currency " . number_format($amount, 2);
}
?>