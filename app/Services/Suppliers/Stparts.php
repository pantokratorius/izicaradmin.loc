<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Stparts implements SupplierInterface
{
    public function getName(): string
    {
        return 'STparts';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://abstd.ru/api-brands", [
            'query' => [
                'number'=> $article,
                'userlogin'=> 'sales@izicar.ru',
                'userpsw'=> '614d6aff2d75d59d509aadf976ab2188',
            ],
        ])->then(function ($response) {
            $json = json_decode($response->getBody()->getContents(), true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item['brand'] ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        return $client->getAsync("https://stparts.ru.public.api.abcp.ru/search/articles", [
            'query' => [
                'number'=> $article,
                'brand'=> $brand,
                'userlogin'=> 'sales@izicar.ru',
                'userpsw'=> '614d6aff2d75d59d509aadf976ab2188',
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);
            
            if (!is_array($json) ) {
                return [];
            }

            return collect($json?? [])->map(function ($item) {
                    return [
                        'name'        => $item['description'] ?? null,
                        'part_make'   => $item['brand'] ?? null,
                        'part_number' => $item['number'] ?? null,
                        'quantity'    => $item['availability'] ?? null,
                        'price'       => $item['price'] ?? null,
                        'delivery'    => ceil($item['deliveryPeriod'] / 24)  ?? null,
                        'warehouse'   => $item['supplierDescription'] ?? null,
                    ];
            })->toArray();
        });
    }
}
