<?php
namespace App\Services\Suppliers;

use App\Models\Part;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Create;

class ATS implements SupplierInterface
{
    public function getName(): string
    {
        return 'ATS-AUTO';
    }

    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        $brands = Part::where('article', $article)
            ->pluck('brand')
            ->unique()
            ->toArray();

        return Create::promiseFor(
            collect($brands)->map(fn($b) => ['brand' => $b])->toArray()
        );
    }

    // Items from DB
    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        $query = Part::query()->where('article', $article);

        if ($brand) {
            $query->where('brand', $brand);
        }

        $items = $query->get()->map(function ($part) {
            return [
                'name'        => $part->name,
                'part_make'   => $part->brand,
                'part_number' => $part->article,
                'quantity'    => $part->quantity,
                'price'       => $part->price,
                'delivery'    => 0,
                'warehouse'   => 'ATS-AUTO',
            ];
        })->toArray();

        return Create::promiseFor($items);
    }
}
