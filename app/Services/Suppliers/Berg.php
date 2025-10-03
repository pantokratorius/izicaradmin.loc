<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Berg implements SupplierInterface
{
    public function getName(): string
    {
        return 'Берг';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        $results = [];


        return new FulfilledPromise($results);
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        return $client->getAsync("https://api.berg.ru/ordering/get_stock.json", [
            'query' => [
                'key'   => 'a6320055ba39df841612509839e11ced99024809f1638af9ee1bfb6abd1d7fd5',
                'items[0][resource_article]'=> $article,
                'items[0][brand_name]'=> $brand,
                'analogs'=> 1,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);
            
            if (!is_array($json) || !isset($json['resources']) || !is_array($json['resources'])) {
                return [];
            }

            return collect($json['resources'] ?? [])->map(function ($item) {
                return collect($item['offers'] ?? [])->map(function ($offer) use ($item) {
                    return [
                        'name'        => $item['name'] ?? null,
                        'part_make'   => $item['brand']['name'] ?? null,
                        'part_number' => $item['article'] ?? null,
                        'quantity'    => $offer['quantity'] ?? null,
                        'price'       => $offer['price'] ?? null,
                        'delivery'    => $offer['assured_period'] ?? null,
                        'warehouse'   => $offer['warehouse']['name'] ?? null,
                    ];
                });
            })->flatten(1)->toArray();
        });
    }
}
