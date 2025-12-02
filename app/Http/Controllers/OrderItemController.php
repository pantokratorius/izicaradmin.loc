<?php

namespace App\Http\Controllers;

use App\Models\BrandGroup;
use App\Models\DraftOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    
    public function index()
    {  
        $items = OrderItem::with('order')->paginate(20);
        return view('order_items.index', compact('items'));
    }   
    
        public function create(Order $order)
        {  
            $suppliers = array_keys(array_filter(Setting::first()->suppliers ?? []));
            $brandGroups = BrandGroup::all();
            $settings = Setting::first();    
            return view('order_items.create', compact('settings', 'brandGroups', 'order', 'suppliers'));
        }
    
        public function create_draft_order(DraftOrder $draftOrder)
        {   
            $suppliers = array_keys(array_filter(Setting::first()->suppliers ?? []));
            $brandGroups = BrandGroup::all();
            $settings = Setting::first();   
            $draft = 1; 
            return view('order_items.create', compact('settings', 'brandGroups', 'draftOrder', 'suppliers', 'draft'));
        }
    
        public function store(Request $request)
        {
            $data = $request->validate([
                'order_id'       => 'required|exists:orders,id',
                'part_number'    => 'nullable|string|max:255',
                'part_make'      => 'nullable|string|max:255',
                'part_name'      => 'nullable|string|max:255',
                'purchase_price' => 'nullable|numeric',
                'sell_price' => 'nullable|numeric',
                'supplier'       => 'nullable|string|max:255',
                
                'quantity'       => 'nullable|integer|min:1',
            ]);

        $data['status'] = 1;
            $item = OrderItem::create($data);

            $order = Order::find($data['order_id']);

           return redirect()->route('orders.show', $order);
            // return view('order_items.index', compact('item'));
        }

public function store_ajax(Request $request)
    {
    try {
        $data = $request->validate([
            'order_id'        => 'required|integer|exists:orders,id',
            'part_number'     => 'required|string|max:255',
            'part_make'       => 'required|string|max:255',
            'name'            => 'nullable|string',
            'quantity'        => 'required|integer|min:1',
            'purchase_price'  => 'nullable|numeric',
            'sell_price'      => 'nullable|string',
            'warehouse'       => 'nullable|string|max:255',
            'supplier'        => 'nullable|string|max:255',
        ]);

        // Normalize
        $data['part_make'] = trim($data['part_make']);
        $data['part_number'] = trim($data['part_number']);
        $data['part_name'] = trim($data['name']);

        // Find existing item for this order
        // $existing = OrderItem::where('order_id', $data['order_id'])
        //     ->where('part_number', $data['part_number'])
        //     ->where('part_make', $data['part_make'])
        //     ->first();

        // if ($existing) {
        //     $existing->quantity += $data['quantity'];
        //     $existing->save();

        //     return response()->json([
        //         'status' => 'updated',
        //         'message' => 'Количество увеличено',
        //         'item' => $existing,
        //     ]);
        // }

        $item = OrderItem::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Товар добавлен в заказ',
            'item' => $item,
        ]);
    } catch (\Throwable $e) {
        Log::error("OrderItem storeAjax error: " . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}





    public function show(OrderItem $orderItem)
    {
        return view('order_items.show', compact('orderItem'));
    }

    public function edit(OrderItem $orderItem)
    {
        return view('order_items.edit', compact('orderItem'))->with('active_tab', 'orders');
    }

    public function update(Request $request, OrderItem $orderitem)
    {
        $data = $request->validate([
            'part_number'    => 'nullable|string|max:255',
            'part_make'      => 'nullable|string|max:255',
            'part_name'      => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric',
            'sell_price' => 'nullable|numeric',
            'supplier'       => 'nullable|string|max:255',
            'quantity'       => 'nullable|integer|min:1',
            'status'         => 'nullable|string|max:255',
            'margin'         => 'nullable|numeric',
        ]);
        $orderitem->update($data);

        return response()->json(['success' => true, 'item' => $orderitem]);
    }
    
    
    public function destroy(OrderItem $orderitem)
    {
        $orderitem->delete();

        return response()->json(['success' => true]);
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $orderItem = OrderItem::findOrFail($id);
        $orderItem->status = $request->status;
        $orderItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Статус успешно обновлён!',
        ]);
    }

    public function batchDelete(Request $request)
    {
        $ids = $request->input('ids');

        if ($ids && is_array($ids)) {
            OrderItem::whereIn('id', $ids)->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }


    public function search(Request $request)
    {
    $query = trim($request->get('q', ''));
    $orders = explode(',', $request->get('orders', ''));

    $items = OrderItem::with(['order:id,order_number'])
        ->whereIn('order_id', $orders) // ✅ restrict to visible orders 
        ->where(function ($q) use ($query) {
            $q->where('part_make', 'like', "%{$query}%")
              ->orWhere('part_number', 'like', "%{$query}%")
              ->orWhere('part_name', 'like', "%{$query}%");
        })
        ->limit(15)
        ->get(['id', 'order_id', 'part_number', 'part_make', 'part_name']);
    return response()->json(
    $items->map(fn($item) => [
        'id' => $item->id,
        'order_id' => $item->order_id,
        'order_number' => $item->order->order_number ?? null,
        'part_number' => $item->part_number,
        'part_make' => $item->part_make,
        'part_name' => $item->part_name,
    ])
);
}


}
