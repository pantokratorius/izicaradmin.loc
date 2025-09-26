<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class AbsSupplier implements SupplierInterface
{
    public function getName(): string
    {
        return 'ABS';
    }

    public function asyncSearch(Client $client, string $article): PromiseInterface
    {
        return $client->getAsync("https://abstd.ru/api-brands", [
            'query' => [
                'auth'   => '3515fab2a59d5d51b91f297a8be3ad5f',
                'article'=> $article,
            ],
        ])->then(function ($response) { print_r($response); exit;
            $json = json_decode($response->getBody()->getContents(), true);

            return collect($json['results'] ?? [])->map(function ($item) {
                return [
                    'name'       => $item ?? '',
                ];
            })->toArray();
        });
    }
}
