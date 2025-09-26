<?php
namespace App\Services;

use App\Services\Suppliers\SupplierInterface;
use App\Services\Suppliers\AbsSupplier;
use App\Services\Suppliers\FakeSupplier;
use App\Services\Suppliers\OtherSupplier;

class SupplierRegistry
{
    public static function all(): array
    {
        return [
            new AbsSupplier(),
            // new OtherSupplier(),
            new FakeSupplier(),
        ];
    }
}
