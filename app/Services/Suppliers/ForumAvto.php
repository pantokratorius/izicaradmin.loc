<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class ForumAvto implements SupplierInterface
{
    public function getName(): string
    {
        return 'Форум-Авто';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://api.forum-auto.ru/v2/listBrands", [
            'query' => [
                'login'   => '466479_bondar',
                'pass'   => 'HRRUOKixvN',
                'art'=> $article,
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
        return $client->getAsync("https://api.forum-auto.ru/v2/listGoods", [
            'query' => [
                'login'   => '466479_bondar',
                'pass'   => 'HRRUOKixvN',
                'cross'   => 1,
                'art'=> $article,
                'br'=> $brand,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);

            if (!is_array($json) || !isset($json['data']) || !is_array($json['data'])) {
                return [];
            }

            return collect($json['data'] ?? [])->map(function ($item) {
                return [
                     'name'        => $item['name'] ?? null,
                    'part_make'   => $item['brand'] ?? null,
                    'part_number' => $item['art'] ?? null,
                    'quantity'    => $item['num'] ?? null,
                    'price'       => $item['price'] ?? null,
                    'delivery'   => $item['d_deliv'] ?? null,
                    'warehouse'   => $item['whse'] ?? null,
                ];
            })->toArray();
        });
    }
}
