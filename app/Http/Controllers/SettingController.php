<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::firstOrCreate(['id' => 1], ['margin' => 20.00]);
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
}
