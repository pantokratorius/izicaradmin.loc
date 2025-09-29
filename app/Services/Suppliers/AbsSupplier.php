<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class AbsSupplier implements SupplierInterface
{
    public function getName(): string
    {
        return 'ABS';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://abstd.ru/api-brands", [
            'query' => [
                'auth'   => '3515fab2a59d5d51b91f297a8be3ad5f',
                'article'=> $article,
            ],
        ])->then(function ($response) {
            $json = json_decode($response->getBody()->getContents(), true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        return $client->getAsync("https://abstd.ru/api-search", [
            'query' => [
                'auth'   => '3515fab2a59d5d51b91f297a8be3ad5f',
                'article'=> $article,
                'brand'=> $brand,
                'with_cross'=> 1,
                'format' => 'json',
                'agreement_id' => 28415,
            ],
        ])->then(function ($response) {
            $json = json_decode($response->getBody()->getContents(), true);
            
            if (!is_array($json) || !isset($json['data']) || !is_array($json['data'])) {
                return [];
            }

            return collect($json['data'] ?? [])->map(function ($item) { 
                return [
                     'name'        => $item['product_name'] ?? null,
                    'part_make'   => $item['brand'] ?? null,
                    'part_number' => $item['article'] ?? null,
                    'quantity'    => $item['quantity'] ?? null,
                    'price'       => $item['price'] ?? null,
                    'warehouse'   => $item['warehouse_name'] ?? null,
                ];
            })->toArray();
        });
    }
}
