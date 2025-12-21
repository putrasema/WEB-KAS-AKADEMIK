<?php
require_once __DIR__ . '/src/Config/init.php';

$db = $db->getConnection();

$rates = [
    'USD' => 16574.585635,
    'EUR' => 19230.769231
];

foreach ($rates as $code => $rate) {
    try {
        $stmt = $db->prepare("UPDATE currencies SET exchange_rate = ? WHERE code = ?");
        $stmt->execute([$rate, $code]);
        echo "Updated $code to $rate\n";
    } catch (Exception $e) {
        echo "Error updating $code: " . $e->getMessage() . "\n";
    }
}
?>