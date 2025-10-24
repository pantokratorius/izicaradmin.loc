<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Stock;
use App\Models\OrderItem;
use App\Models\TempParts;
use Illuminate\Http\Request;

class TempPartsController extends Controller
{
    // 🧾 Show all temporary parts
    public function index()
    {
        $parts = TempParts::orderByDesc('created_at')->get();
        return view('parts.index', compact('parts'));
    }

    // ➕ Add a new part
    public function store(Request $request)
    {
        $data = $request->validate([
            'brand' => 'nullable|string|max:255',
            'article' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'price' => 'nullable|numeric',
            'quantity' => 'nullable|integer',
        ]);

        TempParts::create($data + ['status' => 'pending']);
        return back()->with('success', 'Part added temporarily');
    }

    // 🧭 Move to Stocks
    public function moveToStock(Part $part)
    {
        Stock::create($part->only([
            'brand', 'article', 'name', 'unit', 'price', 'quantity'
        ]));

        $part->update(['status' => 'moved_to_stock']);
        $part->delete();

        return back()->with('success', 'Part moved to Stocks');
    }

    // 📦 Move to Order Items
    public function moveToOrder(Request $request, Part $part)
    {
        $request->validate(['order_id' => 'required|integer|exists:orders,id']);

        OrderItem::create([
            'order_id' => $request->order_id,
            'brand' => $part->brand,
            'article' => $part->article,
            'name' => $part->name,
            'unit' => $part->unit,
            'price' => $part->price,
            'quantity' => $part->quantity,
        ]);

        $part->update(['status' => 'moved_to_order']);
        $part->delete();

        return back()->with('success', 'Part moved to Order Items');
    }

    // 🗑 Delete temporary part
    public function destroy(Part $part)
    {
        $part->delete();
        return back()->with('success', 'Part removed');
    }
}
