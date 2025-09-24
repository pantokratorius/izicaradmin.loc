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
var_dump($response); exit;
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