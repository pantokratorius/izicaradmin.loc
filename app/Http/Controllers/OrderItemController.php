<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    public function index()
    {
        $items = OrderItem::with('order')->paginate(20);
        return view('order_items.index', compact('items'));
    }

    public function create()
    {
        return view('order_items.create');
    }

        public function store(Request $request)
        {
            $data = $request->validate([
                'order_id'       => 'required|exists:orders,id',
                'part_number'    => 'nullable|string|max:255',
                'part_make'      => 'nullable|string|max:255',
                'part_name'      => 'nullable|string|max:255',
                'purchase_price' => 'nullable|numeric',
                'sale_price'     => 'nullable|numeric',
                'supplier'       => 'nullable|string|max:255',
                'prepayment'     => 'nullable|numeric',
                'quantity'       => 'nullable|integer|min:1',
                'status'         => 'nullable|string|max:255',
            ]);

            $item = OrderItem::create($data);

            return response()->json(['success' => true, 'item' => $item]);
        }

    public function show(OrderItem $orderItem)
    {
        return view('order_items.show', compact('orderItem'));
    }

    public function edit(OrderItem $orderItem)
    {
        return view('order_items.edit', compact('orderItem'))->with('active_tab', 'orders');;
    }

    public function update(Request $request, OrderItem $orderitem)
    {

        $data = $request->validate([
            'part_number'    => 'nullable|string|max:255',
            'part_make'      => 'nullable|string|max:255',
            'part_name'      => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric',
            'sale_price'     => 'nullable|numeric',
            'supplier'       => 'nullable|string|max:255',
            'prepayment'     => 'nullable|numeric',
            'quantity'       => 'nullable|integer|min:1',
            'status'         => 'nullable|string|max:255',
        ]);

        $orderitem->update($data);

        return response()->json(['success' => true, 'item' => $orderitem]);
    }

   
public function destroy(OrderItem $orderitem)
{
    $orderitem->delete();

    return response()->json(['success' => true]);
}
}
