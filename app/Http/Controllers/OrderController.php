<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index()
    {
        $orders = Order::with(['client', 'vehicle', 'manager'])->latest()->paginate(15);
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
        return view('orders.create', compact('clients', 'vehicles', 'managers'));
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
            return redirect()->back()->with('success', 'Заказ добавлен');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Заказ не добавлен');
        }

    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['client', 'vehicle', 'manager']);
        return view('orders.show', compact('order'));
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
            return redirect()->back()->with('success', 'Заказ обновлен');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Заказ не обновлен');
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
        $order->load(['vehicle.brand', 'vehicle.client', 'items']);

        return view('orders.print', compact('order'));
    }

    public function print2(Order $order)
    {
        $order->load(['vehicle.brand', 'vehicle.client', 'items']);

        return view('orders.print2', compact('order'));
    }

}
