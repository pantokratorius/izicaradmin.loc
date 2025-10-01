<?php
namespace App\Services;

use App\Services\Suppliers\SupplierInterface;
use App\Services\Suppliers\AbsSupplier;
use App\Services\Suppliers\Berg;
use App\Services\Suppliers\FakeSupplier;
use App\Services\Suppliers\Mosvorechie;

class SupplierRegistry
{
    public static function all(): array
    {
        return [
            new AbsSupplier(),
            // new FakeSupplier(),
            new Mosvorechie(),
            new Berg(),
        ];
    }
}
