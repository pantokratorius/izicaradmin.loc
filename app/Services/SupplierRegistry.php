<?php
namespace App\Services;

use App\Services\Suppliers\SupplierInterface;
use App\Services\Suppliers\AbsSupplier;
use App\Services\Suppliers\Berg;
use App\Services\Suppliers\FakeSupplier;
use App\Services\Suppliers\Favorit;
use App\Services\Suppliers\ForumAvto;
use App\Services\Suppliers\Mikado;
use App\Services\Suppliers\Mosvorechie;
use App\Services\Suppliers\ProfitLiga;
use App\Services\Suppliers\Rossko;

class SupplierRegistry
{
    public static function all(): array
    {
        return [
            new AbsSupplier(),
            // new FakeSupplier(),
            new Mosvorechie(),
            new Berg(),
            new Favorit(),
            new ForumAvto(),
            new ProfitLiga(),
            new Mikado(),
            new Rossko(),
        ];
    }
}
