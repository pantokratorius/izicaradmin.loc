<?php
namespace App\Http\Controllers;

use App\Imports\PartsImport;
use App\Models\Part;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use App\Services\SupplierRegistry;
use GuzzleHttp\Promise\Promise as PromisePromise;
use Maatwebsite\Excel\Facades\Excel;

class PartController extends Controller
{
    public function streamBrands(Request $request)
{
    $article = $request->query('article');
    $client = new Client(['timeout' => 10]);

    $suppliers = SupplierRegistry::all();
    $promises = [];

    foreach ($suppliers as $supplier) {
        $promises[] = $supplier->asyncSearchBrands($client, $article)
            ->then(function ($results) use ($supplier) {
                $brands = collect($results)->pluck('part_make')->filter()->unique()->values();
                echo "event: {$supplier->getName()}\n";
                echo "data: " . json_encode($brands) . "\n\n";
                ob_flush();
                flush();
            });
    }

    return response()->stream(function () use ($promises) {
        Utils::settle($promises)->wait();
        echo "event: end\n";
        echo "data: done\n\n";
        ob_flush();
        flush();
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection'    => 'keep-alive',
    ]);
}

    public function streamItems(Request $request)
    {
        $article = $request->query('article');
        $brand   = $request->query('brand');
        $client  = new Client(['timeout' => 10]);

        $suppliers = SupplierRegistry::all();
        $promises = [];

        foreach ($suppliers as $supplier) {
            $promises[] = $supplier->asyncSearchItems($client, $article, $brand)
                ->then(function ($results) use ($supplier) {
                    // filter by brand
                     echo "event: {$supplier->getName()}\n";
                    echo "data: " . json_encode($results) . "\n\n";
                    ob_flush();
                    flush();
                });
        }

        return response()->stream(function () use ($promises) {
            Utils::settle($promises)->wait();
            echo "event: end\n";
            echo "data: done\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection'    => 'keep-alive',
        ]);
    }

    public function import(Request $request)
{

    set_time_limit(120);

    
    $request->validate([
        'file' => 'required|mimes:xls,xlsx'
    ]);

    Part::truncate();


    Excel::import(new PartsImport, $request->file('file'));

    return redirect()->back()->with('success', 'Parts imported successfully!');
}

}
