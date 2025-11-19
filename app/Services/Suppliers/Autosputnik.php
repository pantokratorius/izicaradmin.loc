<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;

class Autosputnik implements SupplierInterface
{
    public function getName(): string
    {
        return 'Автоспутник';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://newapi.auto-sputnik.ru/products/getbrands", [

             'headers' => [
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiaXppY2FyMiIsInVzZXJpZCI6Ijg4MjEyIiwiZXhwIjoxNzYzNDY0NDk0LCJpc3MiOiJhcGlhdXRvc3B1dG5payIsImF1ZCI6ImFwaWF1dG9zcHV0bmlrY2xpZW50In0.uaWqS5sORBDrv9OzRvQbFvRgaogunBWwmsZavmn5oKE',
            ],
            'query' => [
                // 'options[login]'   => 'izicar2',
                // 'options[pass]'   => '123456',
                'displaycountproduct'   => 'false',
                'articul'   => $article,
            ],
        ])->then(function ($response) {

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);


            // $json = json_decode($response->getBody()->getContents(), true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json['data'] ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item['brand']['name'] ?? '',
                ];
            })->toArray();
        });
    }



public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
{

    return $client->postAsync('https://newapi.auto-sputnik.ru/products/getproducts', [
        'headers' => [
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiaXppY2FyMiIsInVzZXJpZCI6Ijg4MjEyIiwiZXhwIjoxNzYzNDY0NDk0LCJpc3MiOiJhcGlhdXRvc3B1dG5payIsImF1ZCI6ImFwaWF1dG9zcHV0bmlrY2xpZW50In0.uaWqS5sORBDrv9OzRvQbFvRgaogunBWwmsZavmn5oKE',
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json'
        ],
        'json' => [
            'articul' => $article,
            'brand'   => $brand,
            'analogi' => true,
            'tranzit' => false,
        ],
    ])->then(function ($response) {  

        $body = $response->getBody()->getContents();
        $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');


        $json = json_decode($body, true);

        if (!isset($json['data']) || !is_array($json['data'])) {
            return [];
        }

        return collect($json['data'])->map(function ($item) {

            return [
                'name'          => $item['name'] ?? null,
                'part_make'     => $item['brand']['name'] ?? null,
                'part_number'   => $item['articul'] ?? null,
                'quantity'      => $item['quantity'] ?? null,
                'price'         => $item['price'] ?? null,
                'delivery'      => $item['delivery_day'] ?? null,
                'warehouse'     =>'test' ?? null,
            ];

        })->toArray();
    });
}

}



