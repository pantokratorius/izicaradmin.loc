<?php
namespace App\Services\Suppliers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Autoeuro implements SupplierInterface
{
    public function getName(): string
    {
        return 'Авто-Евро';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://api.autoeuro.ru/api/v2/json/search_brands/9vfMBZcrJUizuVBqPkeVcl3cZgjoheUzm2rCFUIGCaQ3XLd35zCebUBSm4Df/", [
            'query' => [
                'code'   => $article,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);


            // $json = json_decode($response->getBody()->getContents(), true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json['DATA'] ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item['brand'] ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        return $client->getAsync("https://api.autoeuro.ru/api/v2/json/search_items/9vfMBZcrJUizuVBqPkeVcl3cZgjoheUzm2rCFUIGCaQ3XLd35zCebUBSm4Df/", [
            'query' => [
                'code'   => $article,
                'brand'   => $brand,
                'with_crosses'   => 1,
                'with_offers'   => 0,
                'delivery_key'   => 'CmThcU5vO6yTcy2YHAUlgA0vlUZgRhN04SG01sixtCpoTjC99FJ165xxzGta89mwhLNonRBxH1vlOg8rjL2xPxAdurElATA',
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);
            
            if (!is_array($json) || !isset($json['DATA']) || !is_array($json['DATA'])) {
                return [];
            }

            return collect($json['DATA'] ?? [])->map(function ($item) {

                    $date = Carbon::parse($item['delivery_time']);
                    $now  = Carbon::now("+03:00");
                    $days = $date->diffInDays($now); 


                    return [
                        'name'        => $item['name'] ?? null,
                        'part_make'   => $item['brand'] ?? null,
                        'part_number' => $item['code'] ?? null,
                        'quantity'    => $item['amount'] ?? null,
                        'price'       => $item['price'] ?? null,
                        'delivery'    => ceil( abs($days) ) ?? null,
                        'warehouse'   => $item['warehouse_name'] ?? null,
                    ];
            })->toArray();
        });
    }
}



