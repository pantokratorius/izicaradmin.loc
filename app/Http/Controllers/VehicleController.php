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
            'client_id' => 'required|exists:clients,id',
            'vin' => 'required|unique:vehicles',
            'vehicle_type' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'year_of_manufacture' => 'required|integer',
            'engine_type' => 'required',
        ]);

        Vehicle::create($request->all());

        return redirect()->back()->with('success', 'Транспортное средство добавлено');
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

        $vehicle->update($request->all());

        return redirect()->back()->with('success', 'Транспортное средство обновлено');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->back()->with('success', 'Транспортное средство удалено');
    }
}
