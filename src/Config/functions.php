<?php
// config/functions.php

function getBaseCurrency($pdo)
{
    $stmt = $pdo->query("SELECT * FROM currencies WHERE is_base = 1 LIMIT 1");
    return $stmt->fetch();
}

function getAllCurrencies($pdo)
{
    $stmt = $pdo->query("SELECT * FROM currencies ORDER BY code");
    return $stmt->fetchAll();
}

function updateExchangeRates($pdo)
{
    // API Key for exchangerate-api.com (Free tier)
    // In a real app, this should be in an env file.
    // Using a public free endpoint if available or just a placeholder for now.
    // For this demo, I'll use a mock function or a real free one if I had a key.
    // I will simulate it for now to avoid key issues, but structure it for real use.

    $base = getBaseCurrency($pdo);
    $baseCode = $base['code'];

    // Example API URL: https://v6.exchangerate-api.com/v6/YOUR-API-KEY/latest/IDR
    // Since I don't have a key, I will simulate random small fluctuations for demo purposes
    // OR use a truly open API like https://open.er-api.com/v6/latest/IDR

    $apiUrl = "https://open.er-api.com/v6/latest/" . $baseCode;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['rates'])) {
            $stmt = $pdo->prepare("UPDATE currencies SET exchange_rate = ? WHERE code = ? AND is_base = 0");
            foreach ($data['rates'] as $code => $rate) {
                // Invert rate because we want 1 Unit = X Base
                // The API gives 1 Base = X Unit.
                // So if Base is IDR, API gives 1 IDR = 0.00006 USD.
                // We want 1 USD = 15000 IDR. So we need 1 / rate.

                if ($rate > 0) {
                    $rateToBase = 1 / $rate;
                    $stmt->execute([$rateToBase, $code]);
                }
            }
            return true;
        }
    }
    return false;
}

function formatCurrency($amount, $currencyCode)
{
    // Simple formatter
    return $currencyCode . ' ' . number_format($amount, 2);
}
?>