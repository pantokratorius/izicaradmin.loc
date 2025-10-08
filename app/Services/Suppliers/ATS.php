<?php
namespace App\Services\Suppliers;

use App\Models\Part;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Log;


class ATS implements SupplierInterface
{
    public function getName(): string
    {
        return 'Ats-Auto';
    }

    /**
     * Search brands in the DB for a given article
     */
    public function asyncSearchBrands(Client $client, string $article): PromiseInterface
    {
        $cleanArticle = $this->cleanArticle($article); 

        $brands = Part::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(part_number, '-', ''), ' ', ''), '_', '') = ?", [$cleanArticle])
            ->pluck('brand')
            ->unique()
            ->toArray();

        return Create::promiseFor(
            collect($brands)->map(fn($b) => ['brand' => $b])->toArray()
        );
    }

    /**
     * Search items in the DB for a given article and optional brand
     */
    public function asyncSearchItems(Client $client, string $article, ?string $brand = null): PromiseInterface
    {
        $cleanArticle = $this->cleanArticle($article);

        $query = Part::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(part_number, '-', ''), ' ', ''), '_', '') = ?", [$cleanArticle]);

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
                'warehouse'   => 'ATC',
            ];
        })->toArray();

        return Create::promiseFor($items);
    }

    /**
     * Clean an article: keep only letters and numbers
     */
    private function cleanArticle(string $article): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $article);
    }
}
