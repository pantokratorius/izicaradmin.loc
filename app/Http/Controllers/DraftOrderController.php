<?php

    namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DraftOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

    class DraftOrderController extends Controller
    {

       

        public function index()
        {
            $orders = DraftOrder::with(['client', 'vehicle', 'manager'])
                ->latest()
                ->paginate(20);

            return view('draft-orders.index', compact('orders'));
        }


        public function create(Request $request)
        {

            $vehicleId = $request->input('vehicle');

            $vehicle = null;
            $client = null;

            if ($vehicleId) {
                $vehicle = Vehicle::with('client')->find($vehicleId);
                if ($vehicle) {
                    $client = $vehicle->client;
                }
            }

            $clients = Client::all();
            $vehicles = Vehicle::all();
            $managers = User::all(); // or filter by role if you have roles
            $orders_count = Order::max('order_number') + 1;
            return view('draft-orders.create', compact('clients', 'vehicles', 'managers', 'orders_count', 'vehicle', 'client'));
        }

        public function show(DraftOrder $draftOrder)
        {
            $globalMargin = Setting::first()->margin ?? 0;
            $draftOrder->load(['client', 'vehicle', 'manager']);
            $clients = Client::all();

            $totalPurchasePrice = $draftOrder->items->sum(fn($item) => $item->purchase_price );
            $totalSellPrice     = $draftOrder->items->sum(fn($item) => $item->sell_price > 0 ? $item->sell_price : $item->amount);
            $totalPurchasePriceSumm = $draftOrder->summ;

            return view('draft-orders.show', compact('draftOrder', 'globalMargin', 'totalPurchasePrice', 'totalSellPrice', 'totalPurchasePriceSumm', 'clients'));
        }

        public function update(Request $request, DraftOrder $draftOrder)
        {
            // session(['active_tab' => 'orders']);
            

            $request->validate([
                'order_number' => 'required|unique:orders,order_number,' . $draftOrder->id,
                'amount'       => 'nullable|numeric',
                'created_at'   => 'nullable|date',
                'vehicle_id'   => 'nullable|exists:vehicles,id',
                'client_id'    => 'nullable|exists:clients,id',
                'prepayment'     => 'nullable|numeric',
                'manager_id'   => 'nullable|exists:users,id',
                'mileage'      => 'nullable|integer',
            ]);

                $returnTo = session('return_to', back());
            try {
                $draftOrder->update($request->all());
                return redirect()->route('draft-orders.index')->with('success', 'Товар добавлен');
                
            } catch (\Throwable $th) {
                return redirect()->route('draft-orders.index')->with('error', 'Заказ не обновлен');
            }

        }

        public function edit(DraftOrder $draftOrder)
        {  
            $clients = Client::all();
            $vehicles = Vehicle::all();
            $managers = User::all();

            session(['return_to' => url()->previous()]);

            return view('draft-orders.edit', compact('draftOrder', 'clients', 'vehicles', 'managers'));
        }


        public function destroy(DraftOrder $draftOrder)
        {
            $draftOrder->delete();
            return redirect()->back()->with('success', 'Черновик удален');
        }


        public function print(DraftOrder $draftOrder, Request $request)
        {
            $draftOrder->load(['client', 'vehicle.brand', 'vehicle.client']);

            // Check if specific items are requested
            if ($request->has('items')) { 
                $itemIds = explode(',', $request->query('items'));
                $items = $draftOrder->items()->whereIn('id', $itemIds)->get();
            } else {
                $items = $draftOrder->items; // all items
            }

            return view('draft-orders.print', compact('draftOrder', 'items'));
        }


        public function print2(DraftOrder $draftOrder, Request $request)
        {
            $draftOrder->load(['client', 'vehicle.brand', 'vehicle.client', 'items']);

            if ($request->has('items')) { 
                $itemIds = explode(',', $request->query('items'));
                $items = $draftOrder->items()->whereIn('id', $itemIds)->get();
            } else {
                $items = $draftOrder->items; // all items
            }

            return view('draft-orders.print2', compact('draftOrder', 'items'));
        }



    }





?>