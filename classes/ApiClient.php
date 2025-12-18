<?php

class ApiClient
{
    private $apiKey;
    private $baseUrl = 'https://v6.exchangerate-api.com/v6/';
    private $db;

    public function __construct()
    {
        // In a real app, load from .env. For now, hardcoded or placeholder.
        // $this->apiKey = getenv('EXCHANGE_RATE_API_KEY'); 
        $this->apiKey = 'YOUR_API_KEY'; // Placeholder
        $this->db = Database::getInstance()->getConnection();
    }

    public function fetchRates($baseCurrency = 'USD')
    {
        // Check cache first (database)
        $stmt = $this->db->prepare("SELECT * FROM currencies WHERE code = ? AND last_updated > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        // This logic is slightly flawed because we want ALL rates relative to base.
        // Simpler approach: Fetch from API, update DB, then read from DB.

        // For this project, let's assume we want to update rates for all active currencies.

        $url = $this->baseUrl . $this->apiKey . '/latest/' . $baseCurrency;

        // Mock response if no API key
        if ($this->apiKey === 'YOUR_API_KEY') {
            return [
                'result' => 'success',
                'conversion_rates' => [
                    'USD' => 1,
                    'IDR' => 15000,
                    'EUR' => 0.92
                ]
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function updateExchangeRates($baseCurrency = 'IDR')
    {
        // We might want to fetch rates relative to IDR if that's our base.
        // Or fetch USD and convert. Let's assume we fetch relative to the base currency setting.

        $data = $this->fetchRates($baseCurrency);

        if ($data && isset($data['conversion_rates'])) {
            $stmt = $this->db->prepare("UPDATE currencies SET exchange_rate = ?, last_updated = NOW() WHERE code = ?");
            foreach ($data['conversion_rates'] as $code => $rate) {
                // Invert rate if necessary? 
                // If base is IDR, and API returns IDR -> USD = 0.00006, then rate is correct.
                // If our DB stores 1 USD = 15000 IDR, that's different.
                // Let's stick to: 1 Unit of Currency = X Base Currency.
                // API usually gives: 1 Base = X Target.
                // If API Base is IDR: 1 IDR = 0.00006 USD.
                // If we want to store how much 1 USD is in IDR: 1/0.00006 = 16666.

                // Let's assume our DB stores "Value in Base Currency".
                // So if Base is IDR. USD row should have ~15000.
                // API (Base IDR) gives USD = 0.000066. 1/0.000066 = 15000.

                if ($rate > 0) {
                    $valueInBase = 1 / $rate;
                    $stmt->execute([$valueInBase, $code]);
                }
            }
        }
    }

    public function convert($amount, $fromCurrency, $toCurrency)
    {
        // Simple conversion using DB rates
        // Rate in DB is "Value in Base".
        // Convert From -> Base -> To.

        $stmt = $this->db->prepare("SELECT exchange_rate FROM currencies WHERE code = ?");

        $stmt->execute([$fromCurrency]);
        $fromRate = $stmt->fetchColumn(); // Value of 1 Unit of From in Base

        $stmt->execute([$toCurrency]);
        $toRate = $stmt->fetchColumn(); // Value of 1 Unit of To in Base

        if ($fromRate && $toRate) {
            // Amount * FromRate = Amount in Base
            // Amount in Base / ToRate = Amount in To
            return ($amount * $fromRate) / $toRate;
        }
        return 0;
    }
}
