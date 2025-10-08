<?php
namespace App\Services\Suppliers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class ProfitLiga implements SupplierInterface
{
    public function getName(): string
    {
        return 'Профит Лига';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
           return $client->getAsync("https://api.pr-lg.ru/search/items", [

            'query' => [
                'article'   => $article,
                'secret'   => 'XCzalwqZVmpsDTSCBqbiwpYLjztMDGhP',
            ],
           
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json  ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item['brand'] ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        return $client->getAsync("https://api.pr-lg.ru/search/crosses", [
            'query' => [
                'secret'   => 'XCzalwqZVmpsDTSCBqbiwpYLjztMDGhP',
                'replaces'   => 1,
                'article'=> $article,
                'brand'=> $brand,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);

            if (!is_array($json)) {
                return [];
            }

           return collect($json ?? [])->map(function ($item) {
                return collect($item['products'] ?? [])->map(function ($offer) use ($item) {

                    $date = Carbon::parse($offer['delivery_date']);
                    $now  = Carbon::now("+03:00");
                    $days = $date->diffInDays($now);


                    return [
                        'name'        => $offer['description'] ?? null,
                        'part_make'   => $item['brand'] ?? null,
                        'part_number' => $item['article'] ?? null,
                        'quantity'    => $offer['quantity'] ?? null,
                        'price'       => $offer['price'] ?? null,
                        'delivery'    =>ceil( abs($days) ) ?? null,
                        'warehouse'   => $offer['custom_warehouse_name'] ?? null,
                    ];
                });
            })->flatten(1)->toArray();
        });
    }
}
