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

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'part_name' => 'required|string|max:255',
            'sale_price' => 'numeric',
            'purchase_price' => 'nullable|numeric',
            'supplier' => 'nullable|string|max:255',
            'prepayment' => 'nullable|numeric',
            'quantity' => 'required|integer|min:1',
        ]);

          if ($validator->fails()) {
                return redirect()->back()
                         ->withErrors($validator)
                         ->withInput()
                         ->with('active_tab', 'orders'); // <-- вот тут
    }


        OrderItem::create($validator->validated());
        return redirect()->back()->with('success', 'Позиция заказа успешно создана.')->with('active_tab', 'orders');
    }

    public function show(OrderItem $orderItem)
    {
        return view('order_items.show', compact('orderItem'));
    }

    public function edit(OrderItem $orderItem)
    {
        return view('order_items.edit', compact('orderItem'))->with('active_tab', 'orders');;
    }

    public function update(Request $request, OrderItem $orderItem)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'part_name' => 'required|string|max:255',
            'sale_price' => 'numeric',
            'purchase_price' => 'numeric',
            'supplier' => 'nullable|string|max:255',
            'prepayment' => 'nullable|numeric',
            'quantity' => 'required|integer|min:1',
        ]);

        $orderItem->update($request->all());
        return redirect()->back()->with('success', 'Позиция заказа обновлена.')->with('active_tab', 'orders');;
    }

    public function destroy($id)
    {
         $orderitem = OrderItem::findOrFail($id);
        $orderitem->delete();
        return redirect()->back()->with('success', 'Позиция заказа удалена.')
        ->with('active_tab', 'orders')
        ->with('toggle-btn-', $orderitem->order_id);
    }
}
