<?php
namespace App\Services\Suppliers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;

class Avtotrade implements SupplierInterface
{
    public function getName(): string
    {
        return 'АвтоТрейд';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {

        $results = [];


        return new FulfilledPromise($results);
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
{
    $url = "https://api2.autotrade.su/?json";

    $request_data = [
        "auth_key" => "c152cde499c6fbfaaac9e62776b7b0a7",
        "method"   => "getStocksAndPrices",
        "withDelivery"   => 1,
        "params"   => [
            "storages" => [0],
            "items"    => [
                $article => $brand ? [$brand => "1"] : 1,
            ],
        ],
    ];

    $body = http_build_query(['data' => json_encode($request_data, JSON_UNESCAPED_UNICODE)]);

    return $client->postAsync($url, [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ],
        'body' => $body,
    ])->then(function ($response) use ($article, $request_data) {
        $raw = $response->getBody()->getContents();
        $json = json_decode($raw, true);

        // optional: debug logging
        Log::info("REQUEST:\n" . json_encode($request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            "RESPONSE:\n" . $raw . "\n\n"
        );

        if (!isset($json['items']) || empty($json['items'])) {
            return [];
        }

        $results = [];

        foreach ($json['items'] as $key => $item) {
            $articleCode = $item['article'] ?? $key;
            $brandName   = $item['brand'] ?? null;
            $itemName    = $item['name'] ?? null;
            $price       = $item['price'] ?? null;

            if (!empty($item['stocks'])) {
                foreach ($item['stocks'] as $stock) {
                    $results[] = [
                        'name'        => $itemName,
                        'part_make'   => $brandName,
                        'part_number' => $articleCode,
                        'quantity'    => $stock['quantity_unpacked'] ?? 0,
                        'price'       => $price,
                        'delivery'    => $stock['delivery_period'] ?? null,
                        'warehouse'   => $stock['name'] ?? null,
                    ];
                }
            }
        }

        return $results;
    });
}



}



