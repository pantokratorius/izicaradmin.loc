<?php

namespace App\Http\Controllers;

use App\Models\BrandGroup;
use App\Models\Search;
use App\Models\Setting;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index()
    { 

        $searchCount = Search::count();
        $brandGroups = BrandGroup::all();
        $settings = Setting::first();
        return view('search.index', compact('settings', 'brandGroups', 'searchCount'));
    }

    public function create()
    { 
        $brandGroups = BrandGroup::all();
        $settings = Setting::first();
        return view('search.create', compact('settings', 'brandGroups'));
    } 

    public function show()
{
    $searches = Search::orderBy('id', 'desc')->paginate(20); 
    $searchCount = Search::count(); // how many rows in the table

    return view('search.show', compact('searchCount', 'searches'));
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'part_make'       => 'nullable|string|max:255',
            'part_number'     => 'nullable|string|max:255',
            'quantity'        => 'integer|min:0',
            'reserved'        => 'integer|min:0',
            'sell_price'      => 'nullable|numeric',
            'warehouse'       => 'nullable|string|max:255',
            'purchase_price'  => 'nullable|numeric',
            'address_name'    => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.string'   => 'Название должно быть строкой.',
            'name.max'      => 'Название не может превышать :max символов.',
        ]);

        Search::create($data);
        return redirect()->route('search.index')->with('success', 'Товар добавлен');
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


        $search = Search::create($data);

        return response()->json([
            'message' => 'Added new search entry successfully',
            'data' => $search,
        ]);
    }



    public function edit(Search $search)
    {
        return view('search.edit', compact('search'));
    }

    public function update(Request $request, Search $search)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'part_make'       => 'nullable|string|max:255',
            'part_number'     => 'nullable|string|max:255',
            'quantity'        => 'integer|min:0',
            'reserved'        => 'integer|min:0',
            'sell_price'      => 'nullable|numeric',
            'warehouse'       => 'nullable|string|max:255',
            'purchase_price'  => 'nullable|numeric',
            'address_name'    => 'nullable|string|max:255',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.string'   => 'Название должно быть строкой.',
            'name.max'      => 'Название не может превышать :max символов.',
        ]);

        $search->update($data);
        return redirect()->route('search.index')->with('success', 'Товар обновлен');
    }

    public function destroy(Search $search)
    {
        $search->delete();
        return redirect()->route('search.index')->with('success', 'Товар удален');
    }

    public function clear(Search $search)
    {
        try {
            Search::truncate();
            return response()->json(['status' => 'ok', 'message' => 'Корзина очищена!']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Корзина не очищена!']);
        }

    }

    public function print()
    { 
        $search = Search::all();

        return view('search.print', compact('search'));
    }

    public function print2(Search $search)
    {
        $search = Search::all();

        return view('search.print2', compact('search'));
    }



}
