<?php
namespace App\Services;

use App\Services\Suppliers\SupplierInterface;
use App\Services\Suppliers\AbsSupplier;
use App\Services\Suppliers\ATS;
use App\Services\Suppliers\Autoeuro;
use App\Services\Suppliers\Autorus;
use App\Services\Suppliers\Autosojuz;
use App\Services\Suppliers\Autosputnik;
use App\Services\Suppliers\Avtotrade;
use App\Services\Suppliers\Berg;
use App\Services\Suppliers\FakeSupplier;
use App\Services\Suppliers\Favorit;
use App\Services\Suppliers\ForumAvto;
use App\Services\Suppliers\Mikado;
use App\Services\Suppliers\Mosvorechie;
use App\Services\Suppliers\ProfitLiga;
use App\Services\Suppliers\Rossko;
use App\Services\Suppliers\Stparts;

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
            new Stparts(),
            new Autorus(),
            new Autosputnik(),
            new Autoeuro(),
            new Autosojuz(),
            new ATS(),
            new Avtotrade(),
        ];
    }
}
