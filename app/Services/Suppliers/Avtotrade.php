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
    $url = "https://api2.autotrade.su/?json";

    $request_data = [
        "auth_key" => "c152cde499c6fbfaaac9e62776b7b0a7",
        "method"   => "getItemsByQuery",
        "params"   => [
            "q" => [$article],
            "strict" => 0,
            "page" => 1,
            "limit" => 100,
            "cross" => 0,
            "replace" => 0,
            "discount" => 1,
            "related" => 0,
            "component" => 0,
            "with_stocks_and_prices" => 0,
            "with_delivery" => 1,
            'storages' => [],
            "filter_brands" => [],
            'filter_part_types' => []
            
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
        $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
        $json = json_decode($raw, true);

        // Debug log (optional)
        // Log::info("REQUEST:\n" . json_encode($request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
        //     "RESPONSE:\n" . $raw . "\n\n" );

        if (!isset($json['brands']) || !is_array($json['brands'])) {
            return [];
        }

        return collect($json['brands'] ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item ?? '',
                ];
            })->toArray();

    });
}


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
{
    $url = "https://api2.autotrade.su/?json"; // assuming same base API URL

    $request_data = [
        "auth_key" => "c152cde499c6fbfaaac9e62776b7b0a7",
        "method"   => "getItemsByQuery",
        "params"   => [
            "q" => [$article],
            "strict" => 0,
            "page" => 1,
            "limit" => 100,
            "cross" => 1,
            "replace" => 1,
            "related" => 0,
            "component" => 0,
            "with_stocks_and_prices" => 1,  
            "with_delivery" => 1,
        ],
    ];

    // if (!empty($brand)) {
    //     // The API expects an array of brand IDs, not names
    //     $request_data["params"]["filter_brands"] = [$brand];
    // }

    // Encode request body
    $body = http_build_query(['data' => json_encode($request_data, JSON_UNESCAPED_UNICODE)]);

    return $client->postAsync($url, [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ],
        'body' => $body,
    ])->then(function ($response) use ($request_data) {
        $raw = $response->getBody()->getContents();
        $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
        $json = json_decode($raw, true);

        // optional debug:
        // Log::info('AUTOTRADE REQUEST: ' . json_encode($request_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        Log::info('AUTOTRADE RESPONSE: ' . $raw);

        // Validate JSON structure
        if (empty($json['items']) || !is_array($json['items'])) {
            return [];
        }

        $results = [];

        foreach ($json['items'] as $item) {
            // There can be no stocks block, handle both cases
            if (!empty($item['stocks'])) {
                foreach ($item['stocks'] as $stock) {
                    $results[] = [
                        'name'        => $item['name'] ?? null,
                        'part_make'   => $item['brand_name'] ?? null,
                        'part_number' => $item['article'] ?? null,
                        'quantity'    => $stock['quantity_unpacked'] ?? 0,
                        'price'       => $item['price'] ?? null,
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



