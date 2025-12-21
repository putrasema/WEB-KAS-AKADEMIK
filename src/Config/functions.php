<?php


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


    $base = getBaseCurrency($pdo);
    $baseCode = $base['code'];



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

    return $currencyCode . ' ' . number_format($amount, 2);
}
?>