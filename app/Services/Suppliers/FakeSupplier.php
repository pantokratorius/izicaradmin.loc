<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\FulfilledPromise;

class FakeSupplier implements SupplierInterface
{
    public function getName(): string
    {
        return 'FakeSupplier';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        // Pretend we make an API call, but just return a static response
        $results = [
            [
                'part_make'   => 'FAKE-BRAND',
            ],
            [
                'part_make'   => 'FAKE-BRAND-2',
            ],
        ];

        // Immediately resolve the promise with fake data
        return new FulfilledPromise($results);
    }

    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        // Pretend we make an API call, but just return a static response
        $results = [
            [
                'name'        => 'Test Part ' . $article,
                'part_make'   => 'FAKE-BRAND',
                'part_number' => $article,
                'quantity'    => 10,
                'price'       => 123.45,
                'warehouse'   => 'Main Warehouse',
            ],
            [
                'name'        => 'Alternative Part ' . $article,
                'part_make'   => 'FAKE-BRAND-2',
                'part_number' => $article . '-ALT',
                'quantity'    => 5,
                'price'       => 99.99,
                'warehouse'   => 'Backup Warehouse',
            ],
        ];

        // Immediately resolve the promise with fake data
        return new FulfilledPromise($results);
    }


}
