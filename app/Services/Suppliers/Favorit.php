<?php
namespace App\Services\Suppliers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Favorit implements SupplierInterface
{
    public function getName(): string
    {
        return 'Фаворит';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        $results = [];


        return new FulfilledPromise($results);
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        return $client->getAsync("https://api.favorit-parts.ru/hs/hsprice", [
            'query' => [
                'key'   => '96FB6B4E-D87C-4EC9-B848-EBE9436A1BEB',
                'number'=> $article,
                'brand'=> $brand,
                'analogs'=> 'on',
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);
            
            if (!is_array($json) || !isset($json['goods']) || !is_array($json['goods'])) {
                return [];
            }

            return collect($json['goods'] ?? [])->map(function ($item) {
                return collect($item['warehouses'] ?? [])

                ->filter(function ($offer) {
                    // keep only warehouses where own === true
                    return isset($offer['own']) && $offer['own'] === true;
                })
                ->map(function ($offer) use ($item) {

                    $date = Carbon::parse($offer['shipmentDate']);
                    $now  = Carbon::now("+03:00");

                    $days = $date->diffInDays($now); 

                    return [
                        'name'        => $item['name'] ?? null,
                        'part_make'   => $item['brand'] ?? null,
                        'part_number' => $item['number'] ?? null,
                        'quantity'    => $offer['stock'] ?? null,
                        'price'       => $offer['price'] ?? null,
                        'delivery'    => ceil( abs($days) ) ?? null,
                        'warehouse'   => $offer['code'] ?? null,
                    ];
                });
            })->flatten(1)->toArray();
        });
    }
}
