<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\OrderItem;
use App\Models\Search;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
 public function index(Request $request)
{
    $query = Order::with(['client', 'vehicle', 'manager']);

    // 🔹 Filter by vehicle
    if ($request->filled('search_by_vehicle')) {
        $search = $request->input('search_by_vehicle');
        $query->whereHas('vehicle', function ($q) use ($search) {
            $q->where('id', $search);
        });
    }

    // 🔹 Full search (orders + order_items)
    if ($request->filled('search')) {
        $search = $request->input('search');

        $query->where(function ($q) use ($search) {
            // Search in orders table
            $q->where('order_number', 'like', "%{$search}%")
              ->orWhereHas('items', function ($sub) use ($search) {
                  $sub->where(function ($s) use ($search) {
                      $s->where('part_name', 'like', "%{$search}%")
                        ->orWhere('part_make', 'like', "%{$search}%")
                        ->orWhere('part_number', 'like', "%{$search}%");
                  });
              });
        });
    }

    $orders = $query->orderBy('id', 'desc')->paginate(15);

    return view('orders.index', compact('orders'));
}


    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $clients = Client::all();
        $vehicles = Vehicle::all();
        $managers = User::all(); // or filter by role if you have roles
        $orders_count = Order::max('order_number') + 1;
        return view('orders.create', compact('clients', 'vehicles', 'managers', 'orders_count'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        session(['active_tab' => 'orders']);

        $request->validate([
            'order_number' => 'required|unique:orders,order_number',
            'amount'       => 'nullable|numeric',
            'created_at'   => 'nullable|date',
            'vehicle_id'   => 'nullable|exists:vehicles,id',
            'prepayment'     => 'nullable|numeric',
            'client_id'    => 'nullable|exists:clients,id',
            'manager_id'   => 'nullable|exists:users,id',
            'mileage'      => 'nullable|integer',
        ]);


        try {
            Order::create($request->all());
            return redirect()->route('orders.index')->with('success', 'Заказ добавлен');

        } catch (\Throwable $th) {
            return redirect()->route('orders.index')->with('error', 'Заказ не добавлен');
        }

    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $totalPurchasePrice = $order->items->sum(fn($item) => $item->purchase_price );
        $totalSellPrice     = $order->items->sum(fn($item) => $item->sell_price > 0 ? $item->sell_price : $item->amount);
        $totalPurchasePriceSumm = $order->summ;

        $globalMargin = Setting::first()->margin ?? 0;
        $order->load(['client', 'vehicle', 'manager']);
        $clients = Client::all();
        return view('orders.show', compact('order', 'globalMargin', 'totalPurchasePrice', 'totalSellPrice', 'totalPurchasePriceSumm', 'clients'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        $clients = Client::all();
        $vehicles = Vehicle::all();
        $managers = User::all();

        return view('orders.edit', compact('order', 'clients', 'vehicles', 'managers'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        session(['active_tab' => 'orders']);


        $request->validate([
            'order_number' => 'required|unique:orders,order_number,' . $order->id,
            'amount'       => 'nullable|numeric',
            'created_at'   => 'nullable|date',
            'vehicle_id'   => 'nullable|exists:vehicles,id',
            'client_id'    => 'nullable|exists:clients,id',
            'prepayment'     => 'nullable|numeric',
            'manager_id'   => 'nullable|exists:users,id',
            'mileage'      => 'nullable|integer',
        ]);


        try {
            $order->update($request->all());
            return redirect()->route('orders.index')->with('success', 'Заказ обновлен');

        } catch (\Throwable $th) {
            return redirect()->route('orders.index')->with('error', 'Заказ не обновлен');
        }

    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        session(['active_tab' => 'orders']);
        $order->delete();
        return redirect()->back()->with('success', 'Заказ удален');
    }


    public function print(Order $order, Request $request)
    {
        $order->load(['client', 'vehicle.brand', 'vehicle.client']);

        // Check if specific items are requested
        if ($request->has('items')) { 
            $itemIds = explode(',', $request->query('items'));
            $items = $order->items()->whereIn('id', $itemIds)->get();
        } else {
            $items = $order->items; // all items
        }

        return view('orders.print', compact('order', 'items'));
    }

    public function print2(Order $order, Request $request)
    {
        $order->load(['client', 'vehicle.brand', 'vehicle.client', 'items']);

          if ($request->has('items')) { 
            $itemIds = explode(',', $request->query('items'));
            $items = $order->items()->whereIn('id', $itemIds)->get();
        } else {
            $items = $order->items; // all items
        }

        return view('orders.print2', compact('order', 'items'));
    }

    public function copy($id)
{
    $order = Order::with('items')->findOrFail($id);

    // Создаём новый заказ на основе старого
    $newOrder = $order->replicate();
    $newOrder->order_number = Order::max('order_number') + 1; // новый номер
    $newOrder->status = 1; // сбрасываем статус
    $newOrder->vehicle_id = $order->vehicle_id; // сбрасываем статус
    $newOrder->client_id = $order->client_id; // сбрасываем статус
    $newOrder->created_at = now();
    $newOrder->updated_at = now();
    $newOrder->save();

    // Копируем позиции
    foreach ($order->items as $item) {
        $newItem = $item->replicate();
        $newItem->order_id = $newOrder->id;
        $newItem->created_at = now();
        $newItem->updated_at = now();
        $newItem->save();
    }
    return redirect()->back()
        ->with('success', 'Заказ успешно скопирован!')->with('copied', $newOrder->id)->with('orders_vehicle', $order->vehicle);
}

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string|max:50',
    ]);

    $order = Order::findOrFail($id);
    $order->status = $request->status;
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Статус успешно обновлён!',
    ]);
}


public function copyToNew(Request $request)
{
    $items = $request->input('rows', []);
    $clientId = $request->input('client_id');
    $vehicleId = $request->input('vehicle_id');

    if (!count($items) || !$clientId ) {
        return response()->json(['error' => 'Missing data'], 422);
    }

    $orders_count = Order::max('order_number') + 1;

    $data = [
        'order_number' => $orders_count,
        'client_id' => $clientId,
        'status' => 1,
    ];
    if($vehicleId){
        $data['vehicle_id'] = $vehicleId;
    }

    $order = Order::create($data);

    OrderItem::whereIn('id', $items)->get()->each(function ($item) use ($order) {
        $order->items()->create($item->toArray());
    });

    return response()->json([
        'redirect' => route('orders.show', $order->id),
    ]);
}


    public function copyToExisting(Request $request, $order_number)
{
    $itemIds = $request->input('ids', []);

    if (!count($itemIds)) {
        return response()->json(['error' => 'No items provided'], 400);
    }

    $order = Order::where('order_number', $order_number)->firstOrFail();

    $items = OrderItem::whereIn('id', $itemIds)->get();
    foreach ($items as $item) {
        $order->items()->create($item->toArray()); // customize as needed
    }

    return response()->json(['success' => true]);
}

public function copyToNew2(Request $request)
{
    $items = $request->input('rows', []);
    $clientId = $request->input('client_id');
    $vehicleId = $request->input('vehicle_id');

    if (!count($items) || !$clientId ) {
        return response()->json(['error' => 'Missing data'], 422);
    }

    $orders_count = Order::max('order_number') + 1;

    $data = [
        'order_number' => $orders_count,
        'client_id' => $clientId,
        'status' => 1,
    ];
    if($vehicleId){
        $data['vehicle_id'] = $vehicleId;
    }

    $order = Order::create($data);

    $rows = $request->input('rows', []); // array of search IDs

    Search::whereIn('id', $rows)->get()->each(function ($search) use ($order) {
        // Convert search record to array (you can filter fields if needed)
        $data = $search->toArray();

        // Optionally remove 'id' or any unwanted fields
        unset($data['id']);

        // Create new item under order->items()
        $order->items()->create($data);
    });

    return response()->json([
        'redirect' => route('orders.show', $order->id),
    ]);
}


    public function copyToExisting2(Request $request, $order_number)
{
    $rowIds = $request->input('ids', []); // get IDs from request

    if (empty($rowIds)) {
        return response()->json(['error' => 'Ничего не выбрано'], 400);
    }

    $order = Order::where('order_number', $order_number)->firstOrFail();

    // get search rows by id
    $searchItems = Search::whereIn('id', $rowIds)->get();

    foreach ($searchItems as $search) {
        $data = $search->toArray();
        unset($data['id']); // remove original ID if necessary
        $order->items()->create($data); // creates linked order_item
    }

    return response()->json(['success' => true]);
}


}
