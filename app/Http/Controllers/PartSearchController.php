<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PartSearchController extends Controller
{
    public function supplier1(Request $request)
    {
        $query = $request->get('q');
        $resp = Http::get("https://supplier1.com/api/parts", [
            'article' => $query,
            'token' => env('SUPPLIER1_KEY'),
        ]);

        $results = [];
        if ($resp->ok()) {
            foreach ($resp->json()['items'] as $item) {
                $results[] = [
                    'supplier' => 'Поставщик 1',
                    'part_number' => $item['code'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['stock'],
                ];
            }
        }

        return response()->json($results);
    }

    public function supplier2(Request $request)
    {
        $query = $request->get('q');
        $resp = Http::get("https://supplier2.com/api/search/{$query}");
        $results = [];
        if ($resp->ok()) {
            foreach ($resp->json() as $item) {
                $results[] = [
                    'supplier' => 'Поставщик 2',
                    'part_number' => $item['article'],
                    'name' => $item['title'],
                    'price' => $item['cost'],
                    'qty' => $item['quantity'],
                ];
            }
        }

        return response()->json($results);
    }
}
