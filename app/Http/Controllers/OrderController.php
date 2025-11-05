<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\OrderItem;
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



        $query = Order::with(['client', 'vehicle', 'manager'])->latest();


        if ($request->filled('search_by_vehicle')) {
            $search = $request->input('search_by_vehicle');

            $query->where(function($q) use ($search) {
                $q->whereHas('vehicle', function($q) use ($search) {
                    $q->where('id', '=', "$search");
                });
            });
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                // Client fields
                $q->where('order_number', $search);
            });
        }

        $orders = $query->paginate(15);

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
        $totalPurchasePriceSumm = $order->items->sum(fn($item) => $item->sell_price > 0 ? $item->sell_price * $item->quantity : $item->amount * $item->quantity);

        $globalMargin = Setting::first()->margin ?? 0;
        $order->load(['client', 'vehicle', 'manager']);
        return view('orders.show', compact('order', 'globalMargin', 'totalPurchasePrice', 'totalSellPrice', 'totalPurchasePriceSumm'));
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


    public function print(Order $order)
    {
        $order->load(['client', 'vehicle.brand', 'vehicle.client', 'items']);

        return view('orders.print', compact('order'));
    }

    public function print2(Order $order)
    {
        $order->load(['client', 'vehicle.brand', 'vehicle.client', 'items']);

        return view('orders.print2', compact('order'));
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
        $itemIds = $request->input('ids', []);

        if (!count($itemIds)) {
            return response()->json(['error' => 'No items provided'], 400);
        }
        $orders_count = Order::max('order_number') + 1;
        $newOrder = Order::create(['status' => 1, 'order_number' => $orders_count]);

        $items = OrderItem::whereIn('id', $itemIds)->get();
        foreach ($items as $item) {
            $newOrder->items()->create($item->toArray()); // customize fields as needed
        }

        return response()->json(['redirect' => route('orders.show', $newOrder)]);
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


}
