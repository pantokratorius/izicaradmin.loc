<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;
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



public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
{
    return Create::promiseFor(null)->then(function () use ($article) {
        $connect = [
            'wsdl' => 'http://api.rossko.ru/service/v2.1/GetSearch',
            'options' => [
                'connection_timeout' => 10,
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
    $soapClient = new SoapClient($connect['wsdl'], $connect['options']); Log::info($soapClient);
    $result = $soapClient->GetSearch($param); Log::info($result);

    // Convert result to XML string
    $responseXml = $soapClient->__getLastResponse();
    if (!$responseXml) {
        return [];
    }

    // Load the full SOAP envelope
    $xml = @simplexml_load_string($responseXml, "SimpleXMLElement", 0, "ns1", true);

    if (!$xml) {
        return [];
    }

    // Navigate to SearchResult
    $searchResult = $xml->xpath('//ns1:SearchResult');
    if (empty($searchResult)) {
        return [];
    }

    // Navigate to PartsList/Part
    $parts = $searchResult[0]->xpath('.//ns1:Part') ?: [];

    return collect($parts)->map(function ($part) {
        $items = collect();

        $stocks = $part->stocks->stock ?? [];
        $stocks = is_array($stocks) ? $stocks : [$stocks];

        foreach ($stocks as $stock) {
            $items->push([
                'name'        => (string)$part->name,
                'part_make'   => (string)$part->brand,
                'part_number' => (string)$part->partnumber,
                'quantity'    => (int)($stock->count ?? 0),
                'price'       => (float)($stock->price ?? 0),
                'delivery'    => isset($stock->delivery) ? (int)$stock->delivery : null,
                'warehouse'   => (string)($stock->description ?? null),
            ]);
        }

        return $items;
    })->flatten(1)->toArray();

} catch (\Exception $e) {
    Log::error('Rossko SOAP API error: ' . $e->getMessage());
    return [];
}
    });
}


}
