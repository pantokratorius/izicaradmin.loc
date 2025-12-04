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

public function index(Request $request)
{
    $query = DB::table('clients')
        ->leftJoin('vehicles', 'clients.id', '=', 'vehicles.client_id')
        ->select('clients.*')
        ->distinct('clients.id');  // To avoid duplicate clients

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            // Client fields
            $q->where('clients.first_name', 'like', "%{$search}%")
            ->orWhere('clients.last_name', 'like', "%{$search}%")
            ->orWhere('clients.middle_name', 'like', "%{$search}%")
            ->orWhere('clients.email', 'like', "%{$search}%")
            ->orWhere('clients.phone', 'like', "%{$search}%")
            // Vehicle fields
            ->orWhere('vehicles.vin', 'like', "%{$search}%")
            ->orWhere('vehicles.vin2', 'like', "%{$search}%")
            ->orWhere('vehicles.brand_name', 'like', "%{$search}%")
            ->orWhere('vehicles.car_brand_id', $search)
            ->orWhere('vehicles.car_model_id', $search)
            ->orWhere('vehicles.car_generation_id', $search);
        });
    }

    if ($request->filled('search_by_vehicle')) {
        $search = $request->input('search_by_vehicle');
        $query->where(function ($q) use ($search) {
            // Client fields
            $q->where('clients.id', $search);
        });
    }
    $clients = $query->orderBy('clients.id', 'desc')->paginate(20)->withQueryString();

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
        'comment'    => $request->comment,
        'created_at'     => now(),
        'updated_at'  => now(),
    ]);

    return redirect()->route('clients.index')->with('success', 'Клиент успешно добавлен!');
}

    // форма редактирования
   public function edit(Request $request, $id)
{
    $client = Client::findOrFail($id);

    // Base query for vehicles
    $vehiclesQuery = $client->vehicles()->with([
    'orders', 
    'brand', 
    'model', 
    'generation', 
    'serie', 
    'modification'
]);

    // Apply search if provided
    if ($search = $request->get('search')) {
        $vehiclesQuery->where(function ($q) use ($search) {
            $q->where('vin', 'like', "%{$search}%")
            ->orWhere('vin2', 'like', "%{$search}%")
            ->orWhere('brand_name', 'like', "%{$search}%")
            ->orWhere('model_name', 'like', "%{$search}%")
            ->orWhere('generation_name', 'like', "%{$search}%")
            ->orWhere('serie_name', 'like', "%{$search}%")
            ->orWhereHas('brand', function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('model', function ($q3) use ($search) {
                $q3->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('generation', function ($q4) use ($search) {
                $q4->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('serie', function ($q5) use ($search) {
                $q5->where('name', 'like', "%{$search}%"); 
            })
            ->orWhereHas('modification', function ($q6) use ($search) {
                $q6->where('name', 'like', "%{$search}%");
            });
        });

        
    }

    // Paginate vehicles
    $vehicles = $vehiclesQuery->paginate(10)->withQueryString(); // keeps ?search=... in pagination links

    // Merge all orders for direct + vehicle orders
    $allOrders = $client->orders->where('status', '>', 0)
        ->merge($vehicles->pluck('orders')->flatten()->where('status', '>', 0))
        ->unique('id')
        ->sortByDesc('created_at')
        ->values();

    $allDraftOrders = $client->draftOrders->where('status',  0)
        ->merge($vehicles->pluck('orders')->flatten()->where('status',  0))
        ->unique('id')
        ->sortByDesc('created_at')
        ->values();



    $brands = CarBrand::orderBy('name')->get();
    $orders_count = Order::max('order_number') + 1;
    $globalMargin = Setting::first()->margin ?? 0;

    return view('clients.edit', compact('client', 'brands', 'orders_count', 'globalMargin', 'allOrders', 'allDraftOrders', 'vehicles'));
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
        'comment'    => $request->comment,
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


public function search(Request $request)
{
    $q = $request->get('q', '');
    $clients = Client::where('first_name', 'like', "%$q%")
        ->orWhere('middle_name', 'like', "%$q%")
        ->orWhere('last_name', 'like', "%$q%")
        ->orWhere('phone', 'like', "%$q%")
        ->limit(20)
        ->get();

    return response()->json(
        $clients->map(fn($c) => [
            'id' => $c->id,
            'text' => $c->first_name . $c->middle_name . $c->last_name . ' (' . $c->phone . ')'
        ])
    );
}


public function vehicles($clientId)
{
    $vehicles = Vehicle::where('client_id', $clientId)->get();
    return response()->json($vehicles);
}


public function list()
{
    $clients = Client::select('id', 'first_name', 'middle_name', 'last_name', 'phone')->get();
    return response()->json($clients);
}

}
