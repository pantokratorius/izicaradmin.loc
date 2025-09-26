<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use App\Services\SupplierRegistry;
use GuzzleHttp\Promise\Promise as PromisePromise;

class PartController extends Controller
{
    public function streamParts(Request $request)
{
    $article = $request->query('article');
    $client = new Client(['timeout' => 10]);

    $suppliers = SupplierRegistry::all();
    $promises = [];

    foreach ($suppliers as $supplier) {
        $promises[] = $supplier->asyncSearch($client, $article)
            ->then(function ($results) use ($supplier) {
                echo "event: {$supplier->getName()}\n";
                echo "data: " . json_encode($results) . "\n\n";
                ob_flush();
                flush();
            });
    }

    return response()->stream(function () use ($promises) {
        Utils::settle($promises)->wait();   // ✅ works in Guzzle 7

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
}
