<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BinanceService
{
    private const BASE_URL = 'https://api.binance.com/api/v3';
    private const TICKER_24HR_URL = self::BASE_URL . '/ticker/24hr';
    private const EXCHANGE_INFO_URL = self::BASE_URL . '/exchangeInfo';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    private function fetchData(string $url): array
    {
        $response = $this->client->request('GET', $url);
        return $response->toArray();
    }

    private function filterUsdtPairs(array $data, array $availableSymbols = []): array
    {
        return array_filter($data, function ($coin) use ($availableSymbols) {
            $isUsdtPair = strpos($coin['symbol'], 'USDT') !== false;
            $isValidSymbol = empty($availableSymbols) || in_array($coin['symbol'], $availableSymbols);
            return $isUsdtPair && $isValidSymbol;
        });
    }

    public function getTopDecliningCoins(int $limit = 9): array
    {
        $data = $this->fetchData(self::TICKER_24HR_URL);
        $usdtPairs = $this->filterUsdtPairs($data);

        usort($usdtPairs, function ($a, $b) {
            return (float)$a['priceChangePercent'] <=> (float)$b['priceChangePercent'];
        });

        return array_slice(array_map([$this, 'formatCoinData'], $usdtPairs), 0, $limit);
    }

    public function getTopVolumeCoinsInUSDT(int $limit = 9): array
    {
        $data = $this->fetchData(self::TICKER_24HR_URL);
        $usdtPairs = $this->filterUsdtPairs($data);

        foreach ($usdtPairs as &$coin) {
            $coin['volumeInUSDT'] = $coin['volume'] * $coin['lastPrice'];
        }
        unset($coin);

        usort($usdtPairs, function ($a, $b) {
            return $b['volumeInUSDT'] <=> $a['volumeInUSDT'];
        });

        return array_slice(array_map([$this, 'formatVolumeCoinData'], $usdtPairs), 0, $limit);
    }

    public function getTopRaisingCoins(int $limit = 9): array
    {
        $exchangeInfo = $this->fetchData(self::EXCHANGE_INFO_URL);
        $availableSymbols = array_column($exchangeInfo['symbols'], 'symbol');

        $data = $this->fetchData(self::TICKER_24HR_URL);
        $usdtPairs = $this->filterUsdtPairs($data, $availableSymbols);

        usort($usdtPairs, function ($a, $b) {
            return (float)$b['priceChangePercent'] <=> (float)$a['priceChangePercent'];
        });

        return array_slice(array_map([$this, 'formatCoinData'], $usdtPairs), 0, $limit);
    }

    public function getTopTransactionCoins(int $limit = 9): array
    {
        $data = $this->fetchData(self::TICKER_24HR_URL);
        $usdtPairs = $this->filterUsdtPairs($data);

        usort($usdtPairs, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return array_slice(array_map([$this, 'formatTransactionCoinData'], $usdtPairs), 0, $limit);
    }

    private function formatCoinData(array $coin): array
    {
        return [
            'symbol' => $coin['symbol'],
            'priceChangePercent' => $coin['priceChangePercent'],
            'lastPrice' => $coin['lastPrice'],
        ];
    }

    private function formatVolumeCoinData(array $coin): array
    {
        return [
            'symbol' => $coin['symbol'],
            'volume' => $coin['volume'],
            'lastPrice' => $coin['lastPrice'],
            'volumeInUSDT' => $coin['volumeInUSDT'],
        ];
    }

    private function formatTransactionCoinData(array $coin): array
    {
        return [
            'symbol' => $coin['symbol'],
            'count' => $coin['count'], // Liczba transakcji
            'lastPrice' => $coin['lastPrice'],
        ];
    }
}
