<?php


/**
 * Format number to currency string
 */
function formatMoney($amount, $currencyCode = 'IDR')
{

    if ($currencyCode === 'IDR') {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    } elseif ($currencyCode === 'USD') {
        return '$' . number_format($amount, 2, '.', ',');
    } elseif ($currencyCode === 'EUR') {
        return '€' . number_format($amount, 2, '.', ',');
    }
    return $currencyCode . ' ' . number_format($amount, 2);
}

/**
 * Get all active currencies
 */
function getCurrencies($pdo)
{
    $stmt = $pdo->query("SELECT * FROM currencies");
    return $stmt->fetchAll();
}

/**
 * Convert amount from one currency to Base Currency (IDR)
 * Uses the rate stored in DB
 */
function convertToBase($amount, $currencyCode, $pdo)
{
    $stmt = $pdo->prepare("SELECT exchange_rate FROM currencies WHERE code = ?");
    $stmt->execute([$currencyCode]);
    $currency = $stmt->fetch();

    if ($currency) {



        return $amount * $currency['exchange_rate'];
    }
    return $amount;
}

/**
 * Update Exchange Rates (Mock implementation for now)
 * In production, use an API like exchangerate-api.com
 */
function updateExchangeRates($pdo)
{


    $rates = [
        'USD' => 15500,
        'EUR' => 16800
    ];

    foreach ($rates as $code => $rate) {
        $stmt = $pdo->prepare("UPDATE currencies SET exchange_rate = ?, last_updated = NOW() WHERE code = ?");
        $stmt->execute([$rate, $code]);
    }

    return true;
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Redirect helper
 */
function redirect($path)
{
    header("Location: " . BASE_URL . $path);
    exit;
}
?>