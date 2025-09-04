<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('client')->get();
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'vin' => 'unique:vehicles',
            'year_of_manufacture' => 'integer|nullable',
        ]);

        session(['active_tab' => 'vehicles']);

        try {
            Vehicle::create($request->all());
            return redirect()->back()->with('success', 'Транспортное средство добавлено');
            
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Транспортное средство не добавлено');
        }
        
    }

    public function show(Vehicle $vehicle)
    {
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'vin' => 'required|unique:vehicles,vin,'.$vehicle->id,
            'vehicle_type' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'year_of_manufacture' => 'required|integer',
            'engine_type' => 'required',
        ]);

        session(['active_tab' => 'vehicles']);

           try {
            $vehicle->update($request->all());
            return redirect()->back()->with('success', 'Транспортное средство обновлено');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Транспортное средство не обновлено');
        }


        

        
    }

    public function destroy(Vehicle $vehicle)
    {

        session(['active_tab' => 'vehicles']);

        $vehicle->delete();
        return redirect()->back()->with('success', 'Транспортное средство удалено');
    }
}
