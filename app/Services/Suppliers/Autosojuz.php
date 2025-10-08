<?php
namespace App\Services\Suppliers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Autosojuz implements SupplierInterface
{
    public function getName(): string
    {
        return 'Авто Союз';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {

        $credentials = base64_encode("Sales@izicar.ru:65d7fk56tu487gA");

        return $client->getAsync("https://xn--80aep1aarf3h.xn--p1ai/SearchService/GetBrands", [

             'headers' => [
                'Authorization' => "Basic $credentials",
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
            'query' => [
                'article'   => $article,
                'withoutTransit'   => true,
            ],
           
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item['Brand'] ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {

        $credentials = base64_encode("Sales@izicar.ru:65d7fk56tu487gA");


        return $client->getAsync("https://xn--80aep1aarf3h.xn--p1ai/SearchService/GetParts", [
            'headers' => [
                'Authorization' => "Basic $credentials",
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
            'query' => [
                'article'   => $article,
                'brand'   => $brand,
                'withoutTransit'   => true,
                'withoutAnalogs'   => true,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);
            
            if (!is_array($json)) {
                return [];
            }

            return collect($json ?? [])->map(function ($item) {


                    return [
                        'name'        => $item['Description'] ?? null,
                        'part_make'   => $item['Brand'] ?? null,
                        'part_number' => $item['Article'] ?? null,
                        'quantity'    => $item['Count'] ?? null,
                        'price'       => $item['CostSale'] ?? null,
                        'delivery'    => ceil($item['SupplierTimeMax'] / 24) ?? null,
                        'warehouse'   => $item['SupplierName'] ?? null,
                    ];
            })->toArray();
        });
    }
}



