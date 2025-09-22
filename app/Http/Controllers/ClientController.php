<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\Client;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    // список клиентов
public function index(Request $request)
{
    $query = DB::table('clients');

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('middle_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    $clients = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();


    return view('clients.index', compact('clients'));
}

    // форма добавления
    public function create()
    {

        return view('clients.create');
    }

    // сохранение нового клиента
   public function store(Request $request)
{
    DB::table('clients')->insert([
        'first_name'  => $request->first_name,
        'last_name'   => $request->last_name,
        'middle_name' => $request->middle_name,
        'phone'       => $request->phone,
        'email'       => $request->email,
        'segment'     => $request->segment,
        'discount'    => $request->discount,
        'created_at'     => now(),
        'updated_at'  => now(),
    ]);

    return redirect()->route('clients.index')->with('success', 'Клиент успешно добавлен!');
}

    // форма редактирования
    public function edit($id)
    {

    $client = Client::with([
        'orders.vehicle',    // orders linked directly + their vehicle
        'vehicles.orders'    // orders linked through vehicles
    ])->findOrFail($id);

    $allOrders = $client->orders
        ->merge($client->vehicles->flatMap->orders)
        ->unique('id') // avoid duplicates if some order is linked twice
        ->values();
        $brands = CarBrand::orderBy('name')->get();
        $orders_count = Order::max('order_number') + 1;
        $globalMargin = Setting::first()->margin ?? 0;
        
        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Клиент не найден');
        }

        return view('clients.edit', compact('client', 'brands', 'orders_count', 'globalMargin', 'allOrders'));
    }

    // обновление клиента
    public function update(Request $request, $id)
{
    DB::table('clients')->where('id', $id)->update([
        'first_name'  => $request->first_name,
        'last_name'   => $request->last_name,
        'middle_name' => $request->middle_name,
        'phone'       => $request->phone,
        'email'       => $request->email,
        'segment'     => $request->segment,
        'discount'    => $request->discount,
        'updated_at'  => now(),
    ]);

    return redirect()->back()->with('success', 'Данные клиента обновлены!');
}

public function destroy($id)
{
    DB::table('clients')->where('id', $id)->delete();

    return redirect()->route('clients.index')->with('success', 'Клиент удален');
}


public function setSessionAjax(Request $request){
    $value = $request->input('active_tab');
    $request->session()->put('active_tab', $value);

    return response()->json([
        'success' => true,
    ]);
}


public function vehicles($clientId)
{
    $vehicles = Vehicle::where('client_id', $clientId)->get();
    return response()->json($vehicles);
}


}
