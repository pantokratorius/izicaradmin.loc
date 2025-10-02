<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Mikado implements SupplierInterface
{
    public function getName(): string
    {
        return 'Микадо';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        $results = [];


        return new FulfilledPromise($results);
    }



public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
{
    return $client->getAsync("https://www.mikado-parts.ru//ws1/service.asmx/Code_Search", [
        'query' => [
            'ClientID'    => '37712',
            'Search_Code' => $article,
            'Password'    => 'zsx23xs2',
            'FromStockOnly'=> 1,
        ],
    ])->then(function ($response) {
        $body = $response->getBody()->getContents();
        $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');

      $xml = simplexml_load_string($body, "SimpleXMLElement", 0, "http://mikado-parts.ru/service");

$result = [];

foreach ($xml->List->Code_List_Row as $item) {
    foreach ($item->OnStocks->StockLine as $stock) {
        $result[] = [
            'name'        => (string) $item->Name,
            'part_make'   => (string) $item->ProducerBrand,
            'part_number' => (string) $item->ProducerCode,
            'quantity'    => (int) $stock->StockQTY,
            'price'       => (float) $item->PriceRUR,
            'delivery'    => (int) $stock->DeliveryDelay,
            'warehouse'   => (string) $stock->StokName,
        ];
    }
}



        return $result;
    });
}



}
