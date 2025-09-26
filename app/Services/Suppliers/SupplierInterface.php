<?php
namespace App\Services\Suppliers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

interface SupplierInterface
{
    public function asyncSearch(Client $client, string $article): PromiseInterface;
    public function getName(): string;
}


