<?php

namespace App\Http\Controllers;

use App\Models\BrandGroup;
use App\Models\Setting;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    { 
        $stocks = Stock::orderBy('id', 'desc')->paginate(20); 
        return view('stocks.index', compact('stocks')); 
    }

    public function create()
    { 
        $brandGroups = BrandGroup::all();
        $settings = Setting::first();
        return view('stocks.create', compact('settings', 'brandGroups'));
    } 

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'part_make'       => 'nullable|string|max:255',
            'part_number'     => 'nullable|string|max:255',
            'quantity'        => 'integer|min:0',
            'volume_step'     => 'nullable|numeric',
            'reserved'        => 'integer|min:0',
            'sell_price'      => 'nullable|numeric',
            'min_stock'       => 'integer|min:0',
            'warehouse'       => 'nullable|string|max:255',
            'warehouse_address' => 'nullable|string|max:255',
            'purchase_price'  => 'nullable|numeric',
            'tags'            => 'nullable|string|max:255',
            'marking'         => 'nullable|string|max:255',
            'categories'      => 'nullable|string|max:255',
            'address_code'    => 'nullable|string|max:255',
            'address_name'    => 'nullable|string|max:255',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.string'   => 'Название должно быть строкой.',
            'name.max'      => 'Название не может превышать :max символов.',
        ]);

        Stock::create($data);
        return redirect()->route('stocks.index')->with('success', 'Товар добавлен');
    }

    public function store_ajax(Request $request)
    {
        $data = $request->validate([
            'part_number' => 'required|string|max:255',
            'part_make' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'warehouse' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
        ]);


// Use a raw query to compare normalized values
$existing = Stock::where('part_number', $data['part_number'])
    ->where('part_make', $data['part_make'])
    ->first();

        if ($existing) {
            $existing->increment('quantity',  1);

            return response()->json([
                'message' => 'Quantity increased successfully',
                'data' => $existing->fresh(),
            ]);
        }

        $stock = Stock::create($data);

        return response()->json([
            'message' => 'Added new stock entry successfully',
            'data' => $stock,
        ]);
    }



    public function edit(Stock $stock)
    {
        return view('stocks.edit', compact('stock'));
    }

    public function update(Request $request, Stock $stock)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'part_make'       => 'nullable|string|max:255',
            'part_number'     => 'nullable|string|max:255',
            'quantity'        => 'integer|min:0',
            'volume_step'     => 'nullable|numeric',
            'reserved'        => 'integer|min:0',
            'sell_price'      => 'nullable|numeric',
            'min_stock'       => 'integer|min:0',
            'warehouse'       => 'nullable|string|max:255',
            'warehouse_address' => 'nullable|string|max:255',
            'purchase_price'  => 'nullable|numeric',
            'tags'            => 'nullable|string|max:255',
            'marking'         => 'nullable|string|max:255',
            'categories'      => 'nullable|string|max:255',
            'address_code'    => 'nullable|string|max:255',
            'address_name'    => 'nullable|string|max:255',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.string'   => 'Название должно быть строкой.',
            'name.max'      => 'Название не может превышать :max символов.',
        ]);

        $stock->update($data);
        return redirect()->route('stocks.index')->with('success', 'Товар обновлен');
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();
        return redirect()->route('stocks.index')->with('success', 'Товар удален');
    }
}
