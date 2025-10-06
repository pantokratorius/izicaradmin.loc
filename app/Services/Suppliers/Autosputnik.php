<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Autosputnik implements SupplierInterface
{
    public function getName(): string
    {
        return 'Автоспутник';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://api.auto-sputnik.ru/search_result.php", [
            'query' => [
                'options[login]'   => 'izicar2',
                'options[pass]'   => '123456',
                'options[datatyp]'   => 'json',
                'data[articul]'   => $article,
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
        return $client->getAsync("https://api.auto-sputnik.ru/search_result.php", [
            'query' => [
                'options[login]'   => 'izicar2',
                'options[pass]'   => '123456',
                'options[datatyp]'   => 'json',
                'options[storage]'   => 'as',
                'data[articul]'   => $article,
                'data[brand]'   => $brand,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);
            
            if (!is_array($json) || !isset($json['requestAnswer']) || !is_array($json['requestAnswer'])) {
                return [];
            }

            return collect($json['requestAnswer'] ?? [])->map(function ($item) {
                    return [
                        'name'        => $item['NAME_TOVAR'] ?? null,
                        'part_make'   => $item['BRA_BRAND'] ?? null,
                        'part_number' => $item['ARTICUL'] ?? null,
                        'quantity'    => $item['STOCK'] ?? null,
                        'price'       => $item['NEW_COST'] ?? null,
                        'delivery'    => $item['DAYOFF'] ?? null,
                        'warehouse'   => $item['PRICE_NAME'] ?? null,
                    ];
            })->toArray();
        });
    }
}



