<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Autorus implements SupplierInterface
{
    public function getName(): string
    {
        return 'Авторусь';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
          return $client->getAsync("https://autorus.ru.public.api.abcp.ru/search/brands", [
            'query' => [
                'number'=> $article,
                'userlogin'=> 'sales@izicar.ru',
                'userpsw'=> '999db4204bb29f7f1c2e679c9a0bac3d',
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
        return $client->getAsync("https://autorus.ru.public.api.abcp.ru/search/articles", [
            'query' => [
                'number'=> $article,
                'brand'=> $brand,
                'userlogin'=> 'sales@izicar.ru',
                'userpsw'=> '999db4204bb29f7f1c2e679c9a0bac3d',
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
                        'delivery'    => $item['deliveryPeriod'] ?? null,
                        'warehouse'   => $item['warehouse']['name'] ?? null,
                    ];
            })->toArray();
        });
    }
}
