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
 
            return collect($json ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        return $client->getAsync("https://abstd.ru/api-brands", [
            'query' => [
                'auth'   => '3515fab2a59d5d51b91f297a8be3ad5f',
                'article'=> $article,
            ],
        ])->then(function ($response) {
            $json = json_decode($response->getBody()->getContents(), true);
 
            return collect($json ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item ?? '',
                ];
            })->toArray();
        });
    }
}
