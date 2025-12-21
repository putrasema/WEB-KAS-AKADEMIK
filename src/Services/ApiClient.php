<?php

class ApiClient
{
    private $apiKey;
    private $baseUrl = 'https://v6.exchangerate-api.com/v6/';
    private $db;

    public function __construct()
    {
        $this->apiKey = 'YOUR_API_KEY';
        $this->db = Database::getInstance()->getConnection();
    }

    public function fetchRates($baseCurrency = 'USD')
    {

        $stmt = $this->db->prepare("SELECT * FROM currencies WHERE code = ? AND last_updated > DATE_SUB(NOW(), INTERVAL 1 HOUR)");




        $url = $this->baseUrl . $this->apiKey . '/latest/' . $baseCurrency;


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


        $data = $this->fetchRates($baseCurrency);

        if ($data && isset($data['conversion_rates'])) {
            $stmt = $this->db->prepare("UPDATE currencies SET exchange_rate = ?, last_updated = NOW() WHERE code = ?");
            foreach ($data['conversion_rates'] as $code => $rate) {


                if ($rate > 0) {
                    $valueInBase = 1 / $rate;
                    $stmt->execute([$valueInBase, $code]);
                }
            }
        }
    }

    public function convert($amount, $fromCurrency, $toCurrency)
    {


        $stmt = $this->db->prepare("SELECT exchange_rate FROM currencies WHERE code = ?");

        $stmt->execute([$fromCurrency]);
        $fromRate = $stmt->fetchColumn();

        $stmt->execute([$toCurrency]);
        $toRate = $stmt->fetchColumn();

        if ($fromRate && $toRate) {

            return ($amount * $fromRate) / $toRate;
        }
        return 0;
    }
}
