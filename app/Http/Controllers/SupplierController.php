<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function searchStream(Request $request)
    {
        $search = $request->query('search', '');

        $suppliers = [
            [
                'name' => 'ABS',
                'api'  => 'https://abstd.ru/api-brands?auth=3515fab2a59d5d51b91f297a8be3ad5f&format=json&article=',
                'api2'  => 'https://abstd.ru/api-search?auth=3515fab2a59d5d51b91f297a8be3ad5f&brand=knecht&with_cross=0&agreement_id=18003&show_unavailable=0&format=json&article=',
            ],
            // [
            //     'name' => 'BERG',
            //     'api'  => 'https://api.berg.ru/references/brands.json?key=a6320055ba39df841612509839e11ced99024809f1638af9ee1bfb6abd1d7fd5',
            // ],
             [
                'name' => 'Moskvorechje',
                'api'  => 'http://portal.moskvorechie.ru/v1/search/brands?l=izicar&p=2FXkfTgXdWU8vXTdxbLuH1Kj9NCWjFgTNQaPW4tnCsyoFReOZWmSBcJmUD9XiF6g&search_oe=1&search_ref=0&search_trade=1&search_ean=0&nsn=1&avail=18&nr=',
            ],
            // Добавьте других поставщиков
        ];

        header('Content-Type: application/json');

        $results = [];

        foreach ($suppliers as $supplier) {
            $url = $supplier['api'] . urlencode($search);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);

            if (curl_errno($ch)) { 
                echo json_encode([
                    'supplier' => $supplier['name'],
                    'error' => curl_error($ch),
                ]) . "\n";
            } else {

                
                $data = json_decode($response, true);
                echo json_encode([
                    'supplier' => $supplier['name'],
                    'data' => $data ?? []
                ]) . "\n";
            }

            flush(); // отправляем сразу клиенту
            curl_close($ch);
        }
    }

    public function getParts(Request $request)
{
    $article = $request->query('article');

    // Конфигурация поставщиков
    $suppliers = [
            [
                'name' => 'ABS',
                'api'  => 'https://abstd.ru/api-search?auth=3515fab2a59d5d51b91f297a8be3ad5f&brand=knecht&with_cross=0&agreement_id=18003&show_unavailable=0&format=json&article=',
                'brand'  => '&brand=',
            ],
            [
                'name' => 'BERG',
                'api'  => 'https://api.berg.ru/references/brands.json?key=a6320055ba39df841612509839e11ced99024809f1638af9ee1bfb6abd1d7fd5',
            ],
           
            // Добавьте других поставщиков
        ];

    $results = [];

    foreach ($suppliers as $supplier) {
        $url = $supplier['api'] . urlencode($article);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $results[] = [
                'supplier' => $supplier['name'],
                'error'    => curl_error($ch),
            ];
        } else {
            $results[] = [
                'supplier' => $supplier['name'],
                'data'     => json_decode($response, true) ?? [],
            ];
        }

        curl_close($ch);
    }

    return response()->json($results);
}




     public function searchParts(Request $request)
    {
        $search = $request->query('search', '');
        $brand = $request->query('brand', '');

        $suppliers = [
            [
                'name' => 'ABS',
                'api'  => 'https://abstd.ru/api-search?auth=3515fab2a59d5d51b91f297a8be3ad5f&brand=knecht&with_cross=0&agreement_id=18003&show_unavailable=0&format=json&article=',
                'brand'  => '&brand=',
            ],
            [
                'name' => 'BERG',
                'api'  => 'https://api.berg.ru/references/brands.json?key=a6320055ba39df841612509839e11ced99024809f1638af9ee1bfb6abd1d7fd5',
            ],
            // Добавьте других поставщиков
        ];

        header('Content-Type: application/json');

        $results = [];

        foreach ($suppliers as $supplier) {
            $url = $supplier['api'] . urlencode($search) . $supplier['brand'] . urlencode($brand);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);

            if (curl_errno($ch)) { 
                echo json_encode([
                    'supplier' => $supplier['name'],
                    'error' => curl_error($ch),
                ]) . "\n";
            } else {

                
                $data = json_decode($response, true);
                echo json_encode([
                    'supplier' => $supplier['name'],
                    'data' => $data ?? []
                ]) . "\n";
            }

            flush(); // отправляем сразу клиенту
            curl_close($ch);
        }
    }


}


?>