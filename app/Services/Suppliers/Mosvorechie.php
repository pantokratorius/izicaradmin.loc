<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;

class Mosvorechie implements SupplierInterface
{
    public function getName(): string
    {
        return 'Москворечье';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {

            $baseUrl = "http://portal.moskvorechie.ru/portal.api";

            // нормальные параметры
            $query = http_build_query([
                'l'   => 'izicar',
                'p'   => '2FXkfTgXdWU8vXTdxbLuH1Kj9NCWjFgTNQaPW4tnCsyoFReOZWmSBcJmUD9XiF6g',
                'act' => 'brand_by_nr',
                 'nr'  => $article,
            ]);


         $url = $baseUrl . '?' . $query ;

        return $client->getAsync($url)->then(function ($response) {
            // Log::info($response->getBody()->getContents());

            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
            $json = json_decode($body, true);

            if (!is_array($json)) {
                return [];
            }
            return collect($json['result'] ?? [])->map(function ($item) {
                return [
                    'part_make'  => $item['brand'] ?? '',
                ];
            })->toArray();
        });
    }


    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {


        $baseUrl = "http://portal.moskvorechie.ru/portal.api";

            // нормальные параметры
            $query = http_build_query([
                'l'   => 'izicar',
                'p'   => '2FXkfTgXdWU8vXTdxbLuH1Kj9NCWjFgTNQaPW4tnCsyoFReOZWmSBcJmUD9XiF6g',
                'act' => 'price_by_nr_firm',
                'nr'  => $article,
                'f'  => $brand,
                'v' => 1,
            ]);


         $url = $baseUrl . '?' . $query . '&avail&extstor&oe';

// Log::info( $url);

        return $client->getAsync($url)->then(function ($response) {
            $body = $response->getBody()->getContents();
            $body = mb_convert_encoding($body, 'UTF-8', 'CP1251');
            $json = json_decode($body, true);

            if (!is_array($json) || !isset($json['result']) || !is_array($json['result'])) {
                return [];
            }

            return collect($json['result'] ?? [])->map(function ($item) {
                return [
                     'name'        => $item['name'] ?? null,
                    'part_make'   => $item['brand'] ?? null,
                    'part_number' => $item['nr'] ?? null,
                    'quantity'    => $item['stock'] ?? null,
                    'price'       => $item['price'] ?? null,
                    'delivery'   => $item['sname'] ?? null,
                    'warehouse'   => $item['warehouse_name'] ?? null,
                ];
            })->toArray();
        });
    }
}
