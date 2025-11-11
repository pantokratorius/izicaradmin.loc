<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'margin' => 'required|numeric|min:0|max:100',
        ]);

        $setting = Setting::first();
        $setting->update($data);

        return redirect()->route('settings.edit')->with('success', 'Настройки обновлены!');
    }


    public function updatePercent(Request $request)
    {
         $request->validate([
        'percent' => 'required|numeric|min:0|max:100',
    ]);

    DB::table('settings')->update([
        'percent' => $request->percent,
    ]);

        $settings = DB::table('settings')->first();
        $percent = $settings ? round($settings->percent, 0) : 0;
        if(!($percent > 0)) $percent = $settings->margin;

        return response()->json(['success' => true, 'message' => 'Проценты обновлены!', 'value' => $percent]);
    }

    public function updateSuppliers(Request $request)
{
    $setting = Setting::first();
    $suppliers = $setting->suppliers ?? [];

    // Mark submitted checkboxes as active
    foreach ($request->input('suppliers', []) as $name => $value) {
        $suppliers[$name] = true;
    }

    // Mark unchecked suppliers as inactive
    foreach ($suppliers as $name => $value) {
        if (!isset($request->suppliers[$name])) {
            $suppliers[$name] = false;
        }
    }

    // Add new supplier if submitted
    if ($new = trim($request->input('new_supplier'))) {
        $suppliers[$new] = true;
    }

    $setting->suppliers = $suppliers;
    $setting->save();


    return back()->with('success', 'Поставщики обновлены.');
}


}
