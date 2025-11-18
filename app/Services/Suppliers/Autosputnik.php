<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;

class Autosputnik implements SupplierInterface
{
    public function getName(): string
    {
        return 'Автоспутник';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://newapi.auto-sputnik.ru/products/getbrands", [

             'headers' => [
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiaXppY2FyMiIsInVzZXJpZCI6Ijg4MjEyIiwiZXhwIjoxNzYzNDY0NDk0LCJpc3MiOiJhcGlhdXRvc3B1dG5payIsImF1ZCI6ImFwaWF1dG9zcHV0bmlrY2xpZW50In0.uaWqS5sORBDrv9OzRvQbFvRgaogunBWwmsZavmn5oKE',
            ],
            'query' => [
                'displaycountproduct'   => 'false',
                'articul'   => $article,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);


            // $json = json_decode($response->getBody()->getContents(), true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json['requestAnswer'] ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item['BRA_BRAND'] ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
{
    $formParams = [
        'options' => [
            'login'   => 'izicar2',
            'pass'    => '123456',
            'datatyp' => 'JSON',
            'storage' => 'as',
        ],
        'data' => [
            'articul' => $article,
        ],
    ];

    if (!empty($brand)) {
        $formParams['data']['brand'] = $brand;
    }

    return $client->postAsync('https://api.auto-sputnik.ru/search_result.php', [
        'form_params' => $formParams,
    ])->then(function ($response) use ($formParams) {
        $body = $response->getBody()->getContents();
        $body = mb_convert_encoding($body, 'UTF-8', 'Windows-1251');

        $json = json_decode($body, true);
        // Log::info("aaaaaaaaaaaaaaaa:\n" .  json_encode($formParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)  . "\n" . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" 
            
        // );

        if (empty($json['requestAnswer']) || !is_array($json['requestAnswer'])) {
            return [];
        }

        return collect($json['requestAnswer'])->map(function ($item) {
            return [
                'name'        => $item['NAME_TOVAR'] ?? null,
                'part_make'   => $item['BRA_BRAND'] ?? null,
                'part_number' => $item['ARTICUL'] ?? null,
                'quantity'    => round($item['STOCK'], 0) ?? null,
                'price'       => $item['NEW_COST'] ?? null,
                'delivery'    => $item['DAYOFF'] ?? null,
                'warehouse'   => $item['PRICE_NAME'] ?? null,
            ];
        })->toArray();
    });
}
}



