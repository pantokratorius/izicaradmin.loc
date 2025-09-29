<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;

interface SupplierInterface
{
    public function asyncSearchBrands(Client $client, string $article);
    public function asyncSearchItems(Client $client, string $article, ?string $brand = null);
    public function getName(): string;
}


