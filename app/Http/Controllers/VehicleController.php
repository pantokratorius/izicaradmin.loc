<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\CarGeneration;
use App\Models\CarModel;
use App\Models\CarModification;
use App\Models\CarSerie;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('vehicles');
        $vehicles = Vehicle::with(['brand', 'model', 'generation', 'modification'])
        ->orderBy('id', 'desc')
    ->paginate(20);

    if ($request->filled('search')) {
        $search = $request->input('search');
        // $query->where(function ($q) use ($search) {
        //     $q->where('first_name', 'like', "%{$search}%")
        //       ->orWhere('last_name', 'like', "%{$search}%")
        //       ->orWhere('middle_name', 'like', "%{$search}%")
        //       ->orWhere('email', 'like', "%{$search}%")
        //       ->orWhere('phone', 'like', "%{$search}%");
        // });
    }

    return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        session(['active_tab' => 'vehicles']);
        $request->validate([
            'vin' => 'unique:vehicles|nullable',
            'year_of_manufacture' => 'integer|nullable',
        ]);


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

        // dd($vehicle->brand->id);
        // $vehicle = Vehicle::with(['brand', 'model', 'generation', 'serie', 'modification'])
        //     ->findOrFail($vehicle->id);

        // instead of manually pulling all, you can also:
        $brands = CarBrand::all();
        $models = CarModel::where('car_brand_id', $vehicle->brand_id)->get();
        $generations = CarGeneration::where('car_model_id', $vehicle->model_id)->get();
        $series = CarSerie::where('car_generation_id', $vehicle->generation_id)->get();
        $modifications = CarModification::where('car_serie_id', $vehicle->serie_id)->get();

        return view('vehicles.edit', compact(
            'vehicle', 'brands', 'models', 'generations', 'series', 'modifications'
        ));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        session(['active_tab' => 'vehicles']);
        $request->validate([
        'vin' => [
            Rule::unique('vehicles', 'vin')->ignore($vehicle->id),
        ],
        'year_of_manufacture' => 'integer|nullable',
    ]);


           try {
            $vehicle->update($request->all());
            return redirect()->route('vehicles.index')->with('success', 'Транспортное средство обновлено');

        } catch (\Throwable $th) {
            return redirect()->route('vehicles.index')->with('error', 'Транспортное средство не обновлено');
        }


    }

    public function destroy(Vehicle $vehicle)
    {

        session(['active_tab' => 'vehicles']);

        $vehicle->delete();
        return redirect()->back()->with('success', 'Транспортное средство удалено');
    }

    




}
