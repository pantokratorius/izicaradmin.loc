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
    // Wrap the blocking SoapClient call into a promise
    return Create::promiseFor(null)->then(function () use ($article, $brand) {

        $connect = [
            'wsdl' => 'http://api.rossko.ru/service/v2.1/GetSearch',
            'options' => [
                'connection_timeout' => 1,
                'trace' => true,
                // 'exceptions' => true,
            ]
        ];

        $param = [
            'KEY1' => '3289f946550041e90fbb5f8a3c9a3abd',
            'KEY2' => '6e988b07e0e96dd3713051dca14f81ec',
            'text' => $article,
            'delivery_id' => '000000002',
            'address_id' => '63987'
        ];

        try {
            $clientSoap = new SoapClient($connect['wsdl'], $connect['options']);  
            $result = $clientSoap->GetSearch($param);

            $partsList = $result->SearchResult->PartsList->Part ?? [];
            $results = [];

            foreach ($partsList as $part) {
                $results = array_merge($results, $this->extractPartStocks($part));

                if (!empty($part->crosses->Part)) {
                    foreach ($part->crosses->Part as $cross) {
                        $results = array_merge($results, $this->extractPartStocks($cross));
                    }
                }
            }

            return $results;

        } catch (SoapFault $e) {
            Log::error("Rossko SOAP async error: " . $e->getMessage());
            return [];
        }
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
