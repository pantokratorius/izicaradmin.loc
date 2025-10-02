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
    try {
        $connect = [
            'wsdl' => 'http://api.rossko.ru/service/v2.1/GetSearch',
            'options' => [
                'connection_timeout' => 1,
                'trace' => true
            ]
        ];

        $param = [
            'KEY1'        => '3289f946550041e90fbb5f8a3c9a3abd',
            'KEY2'        => '6e988b07e0e96dd3713051dca14f81ec',
            'text'        => $article,
            'delivery_id' => '000000002',
            'address_id'  => '63987'
        ];

        $soap = new SoapClient($connect['wsdl'], $connect['options']);
        $result = $soap->GetSearch($param);

        $items = [];

        if ($result->SearchResult && $result->SearchResult->success == 1) {
            $parts = $this->toArray($result->SearchResult->PartsList->Part ?? []);

            foreach ($parts as $part) {
                foreach ($this->toArray($part->stocks->stock ?? []) as $stock) {
                    $items[] = $this->mapItem($part, $stock);
                }

                foreach ($this->toArray($part->crosses->Part ?? []) as $cross) {
                    foreach ($this->toArray($cross->stocks->stock ?? []) as $stock) {
                        $items[] = $this->mapItem($cross, $stock);
                    }
                }
            }
        }

          // ✅ применяем два правила фильтрации
            $items = array_filter($items, function ($p) {
                return $p['quantity'] > 0
                    && (stripos($p['warehouse'] ?? '', 'партнерский') === false);
            });

        // сортировка
        usort($items, fn($a, $b) => $b['quantity'] <=> $a['quantity']);

        // Возвращаем уже FulfilledPromise
        return new FulfilledPromise($items);

    } catch (\Throwable $e) {
        // если ошибка → возвращаем rejected promise
            error_log($e->getMessage());
    if (isset($soap)) {
        error_log($soap->__getLastRequestHeaders());
        error_log($soap->__getLastRequest());
        error_log($soap->__getLastResponseHeaders());
        error_log($soap->__getLastResponse());
    }
    throw $e;
    }
}

private function toArray($value): array
{
    if (empty($value)) return [];
    return is_array($value) ? $value : [$value];
}

private function mapItem($part, $stock): array
{
    return [
        'part_number'     => (string)$part->partnumber,
        'part_make'   => (string)$part->brand,
        'name'    => (string)$part->name,
        'price'   => (float)$stock->price,
        'quantity'     => (int)$stock->count,
        'delivery' => (string)$stock->delivery,
        'warehouse'     => (string)$stock->description,
    ];
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
