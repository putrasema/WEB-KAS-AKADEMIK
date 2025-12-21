<?php

class CurrencyService
{
    private $baseUrl = 'https://api.frankfurter.app';
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = getenv('EXCHANGE_RATE_API_KEY');
    }

    /**
     * Fetch historical trend data for a currency pair.
     * 
     * @param string $from Base currency (e.g., 'USD')
     * @param string $to Target currency (e.g., 'IDR')
     * @param int $days Number of days to look back
     * @return array Array of dates and rates, or empty array on failure
     */
    private $cacheFile = __DIR__ . '/../../cache/currency_data.json';

    /**
     * Fetch historical trend data for a currency pair.
     * 
     * @param string $from Base currency (e.g., 'USD')
     * @param string $to Target currency (e.g., 'IDR')
     * @param int $days Number of days to look back
     * @return array Array of dates and rates, or empty array on failure
     */
    public function getTrend($from = 'USD', $to = 'IDR', $days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        $endDate = date('Y-m-d');


        if (!is_dir(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0777, true);
        }


        $cacheKey = "{$from}_{$to}_{$days}_{$endDate}";
        if (file_exists($this->cacheFile)) {
            $cacheContent = json_decode(file_get_contents($this->cacheFile), true);
            if (isset($cacheContent['key']) && $cacheContent['key'] === $cacheKey && isset($cacheContent['data'])) {
                return $cacheContent['data'];
            }
        }


        $url = "{$this->baseUrl}/{$startDate}..{$endDate}?from={$from}&to={$to}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['rates'])) {
                $trend = [];

                ksort($data['rates']);

                foreach ($data['rates'] as $date => $rates) {
                    if (isset($rates[$to])) {
                        $trend[] = [
                            'date' => $date,
                            'rate' => $rates[$to]
                        ];
                    }
                }


                if (!empty($trend)) {
                    $lastItem = end($trend);
                    $lastDate = $lastItem['date'];
                    if ($lastDate < $endDate) {

                        $current = strtotime($lastDate);
                        $endTimestamp = strtotime($endDate);
                        while ($current < $endTimestamp) {
                            $current = strtotime('+1 day', $current);
                            if ($current <= $endTimestamp) {
                                $trend[] = [
                                    'date' => date('Y-m-d', $current),
                                    'rate' => $lastItem['rate']
                                ];
                            }
                        }
                    }
                }


                file_put_contents($this->cacheFile, json_encode([
                    'key' => $cacheKey,
                    'timestamp' => time(),
                    'data' => $trend
                ]));

                return $trend;
            }
        }


        return [];
    }
}
