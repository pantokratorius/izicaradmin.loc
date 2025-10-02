<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use SoapClient;
use SoapFault;

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
    $xmlBody = <<<XML
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GetSearch xmlns="http://api.rossko.ru/">
      <KEY1>3289f946550041e90fbb5f8a3c9a3abd</KEY1>
      <KEY2>6e988b07e0e96dd3713051dca14f81ec</KEY2>
      <text>{$article}</text>
      <delivery_id>000000002</delivery_id>
      <address_id>63987</address_id>
    </GetSearch>
  </soap:Body>
</soap:Envelope>
XML;

    return $client->postAsync('http://api.rossko.ru/service/v2.1/GetSearch', [
        'headers' => [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction'   => 'http://api.rossko.ru/GetSearch'
        ],
        'body' => $xmlBody,
        'timeout' => 5
    ])->then(function ($response) {
        $xml = simplexml_load_string((string)$response->getBody());
Log::info($xml);
        // SOAP → массив (как было у тебя)
        $result = $xml->xpath('//SearchResult')[0] ?? null;

        $items = [];
        if ($result && (string)$result->success === "1") {
            $parts = $result->PartsList->Part ?? [];
            if (!is_array($parts)) {
                $parts = [$parts];
            }

            foreach ($parts as $part) {
                // stocks
                if (!empty($part->stocks->stock)) {
                    $stocks = is_array($part->stocks->stock) ? $part->stocks->stock : [$part->stocks->stock];
                    foreach ($stocks as $stock) {
                        $items[] = [
                            'art'     => (string)$part->partnumber,
                            'brand'   => (string)$part->brand,
                            'name'    => (string)$part->name,
                            'price'   => (float)$stock->price,
                            'num'     => (int)$stock->count,
                            'd_deliv' => (string)$stock->delivery,
                            'whs'     => (string)$stock->description
                        ];
                    }
                }

                // crosses
                if (!empty($part->crosses->Part)) {
                    $crossParts = is_array($part->crosses->Part) ? $part->crosses->Part : [$part->crosses->Part];
                    foreach ($crossParts as $cross) {
                        if (!empty($cross->stocks->stock)) {
                            $stocks = is_array($cross->stocks->stock) ? $cross->stocks->stock : [$cross->stocks->stock];
                            foreach ($stocks as $stock) {
                                $items[] = [
                                    'art'     => (string)$cross->partnumber,
                                    'brand'   => (string)$cross->brand,
                                    'name'    => (string)$cross->name,
                                    'price'   => (float)$stock->price,
                                    'num'     => (int)$stock->count,
                                    'd_deliv' => (string)$stock->delivery,
                                    'whs'     => (string)$stock->description
                                ];
                            }
                        }
                    }
                }
            }
        }

        // сортировка (по количеству например)
        usort($items, function ($a, $b) {
            return $b['num'] <=> $a['num'];
        });

        return $items;
    });
}

// Adjusted extractPartStocks for SoapClient response
protected function extractPartStocks($part): array
{
    $result = [];
    $partName   = (string) $part->name;
    $partBrand  = (string) $part->brand;
    $partNumber = (string) $part->partnumber;

    if (!empty($part->stocks->stock)) {
        foreach ($part->stocks->stock as $stock) {
            $result[] = [
                'name'        => $partName,
                'part_make'   => $partBrand,
                'part_number' => $partNumber,
                'quantity'    => (int) $stock->count,
                'price'       => (float) $stock->price,
                'delivery'    => (int) $stock->delivery,
                'warehouse'   => (string) $stock->description,
            ];
        }
    } else {
        $result[] = [
            'name'        => $partName,
            'part_make'   => $partBrand,
            'part_number' => $partNumber,
            'quantity'    => 0,
            'price'       => null,
            'delivery'    => null,
            'warehouse'   => null,
        ];
    }

    return $result;
}


}
