<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use SoapClient;

class Rossko implements SupplierInterface
{
    public function getName(): string
    {
        return 'Росско';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        $results = [];


        return new FulfilledPromise($results);
    }



public function asyncSearchItems(Client $client, string $article, ?string $brand = null): array
{
    // Rossko SOAP credentials
    $connect = [
        'wsdl' => 'http://api.rossko.ru/service/v2.1/GetSearch',
        'options' => [
            'connection_timeout' => 1,
            'trace' => true,
        ],
    ];

    $param = [
        'KEY1'       => '3289f946550041e90fbb5f8a3c9a3abd',
        'KEY2'       => '6e988b07e0e96dd3713051dca14f81ec',
        'text'       => $article,
        'delivery_id'=> '000000002',
        'address_id' => '63987',
    ];

    try {
        $soapClient = new \SoapClient($connect['wsdl'], $connect['options']);
        $soapClient->GetSearch($param);

        $xml = simplexml_load_string($soapClient->__getLastResponse());
        $xml->registerXPathNamespace('ns1', 'http://api.rossko.ru/');

        $parts = $xml->xpath('//ns1:Part') ?: [];

        return collect($parts)->map(function ($part) use ($brand) {

            // Skip parts that don't match brand if specified
            // if ($brand && mb_strtolower($brand) !== mb_strtolower((string)$part->brand)) {
            //     return collect();
            // }

            // Handle stocks
            $stocks = $part->stocks->stock ?? [];
            if (!is_array($stocks)) {
                $stocks = [$stocks]; // wrap single element
            }

            $items = collect($stocks)->map(function ($stock) use ($part) {
                return [
                    'name'        => (string)$part->name,
                    'part_make'   => (string)$part->brand,
                    'part_number' => (string)$part->partnumber,
                    'quantity'    => (int)$stock->count,
                    'price'       => (float)$stock->price,
                    'delivery'    => (int)$stock->delivery,
                    'warehouse'   => (string)$stock->description,
                ];
            });

            // Handle crosses
            if (!empty($part->crosses->Part)) {
                $crosses = $part->crosses->Part;
                if (!is_array($crosses)) {
                    $crosses = [$crosses]; // wrap single element
                }

                foreach ($crosses as $cross) {
                    if ($brand && mb_strtolower($brand) !== mb_strtolower((string)$cross->brand)) {
                        continue;
                    }

                    $crossStocks = $cross->stocks->stock ?? [];
                    if (!is_array($crossStocks)) {
                        $crossStocks = [$crossStocks]; // wrap single element
                    }

                    $crossItems = collect($crossStocks)->map(function ($stock) use ($cross) {
                        return [
                            'name'        => (string)$cross->name,
                            'part_make'   => (string)$cross->brand,
                            'part_number' => (string)$cross->partnumber,
                            'quantity'    => (int)$stock->count,
                            'price'       => (float)$stock->price,
                            'delivery'    => (int)$stock->delivery,
                            'warehouse'   => (string)$stock->description,
                        ];
                    });

                    $items = $items->merge($crossItems);
                }
            }

            return $items;
        })->flatten(1)->toArray();

    } catch (\Exception $e) {
        // Return empty array on error
        return [];
    }
}

}
