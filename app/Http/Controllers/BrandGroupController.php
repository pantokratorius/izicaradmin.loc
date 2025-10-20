<?php

namespace App\Http\Controllers;

use App\Models\BrandGroup;
use Illuminate\Http\Request;

class BrandGroupController extends Controller
{
    public function index()
    {
        $groups = BrandGroup::orderBy('display_name')->get();
        return view('brand_groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string',
            'aliases' => 'nullable|string',
        ]);

        BrandGroup::create([
            'display_name' => $request->display_name,
            'aliases' => array_filter(array_map('trim', explode(',', $request->aliases ?? ''))),
        ]);

        return back()->with('success', 'Бренд добавлен.');
    }

    public function update(Request $request, BrandGroup $brandGroup)
    {
        $request->validate([
            'display_name' => 'required|string',
            'aliases' => 'nullable|string',
        ]);

        $brandGroup->update([
            'display_name' => $request->display_name,
            'aliases' => array_filter(array_map('trim', explode(',', $request->aliases ?? ''))),
        ]);

        return back()->with('success', 'Изменения сохранены.');
    }

    public function destroy(BrandGroup $brandGroup)
    {
        $brandGroup->delete();
        return back()->with('success', 'Бренд удалён.');
    }

    public function updateAjax(Request $request)
    {
        $group = BrandGroup::findOrFail($request->id);
        $group->display_name = $request->display_name;
        $group->aliases = explode(',', $request->grouped_names); // save as array/json
        $group->save();

        return response()->json(['success' => true]);
    }
}
