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
        //     $vehicles = Vehicle::with(['brand', 'model', 'generation', 'modification'])
        //     ->orderBy('id', 'desc')
        // ->paginate(20);
        
    $query = Vehicle::with(['brand', 'model', 'generation', 'serie', 'modification', 'client']);

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('brand_name', 'like', "%{$search}%")
              ->orWhere('model_name', 'like', "%{$search}%")
              ->orWhere('vin', 'like', "%{$search}%")
              ->orWhere('generation_name', 'like', "%{$search}%")
              ->orWhere('serie_name', 'like', "%{$search}%")
              ->orWhere('modification_name', 'like', "%{$search}%")
              ->orWhereHas('brand', function ($q) use ($search) {
              $q->where('name', 'like', "%{$search}%");
          })
          ->orWhereHas('model', function ($q) use ($search) {
              $q->where('name', 'like', "%{$search}%");
          })
          ->orWhereHas('generation', function ($q) use ($search) {
              $q->where('name', 'like', "%{$search}%");
          });
        });
    }
    $vehicles = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

    return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $brands = CarBrand::all(); // Fetch all car brands
        return view('vehicles.create', compact('brands'));
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

        $previousUrl = url()->previous();
        $previousRoute = app('router')->getRoutes()->match(app('request')->create($previousUrl));
        if($previousRoute->getName() == 'vehicles.edit'){
           try {
            $vehicle->update($request->all());
            return redirect()->route('vehicles.index')->with('success', 'Транспортное средство обновлено');

            } catch (\Throwable $th) {
                return redirect()->route('vehicles.index')->with('error', 'Транспортное средство не обновлено');
            }
        }else {
            try {
            $vehicle->update($request->all());
            return redirect()->back()->with('success', 'Транспортное средство обновлено');

            } catch (\Throwable $th) {
                return redirect()->back()->with('error', 'Транспортное средство не обновлено');
            }
        }
        

    }


    public function search(Request $request)
{
    $q = $request->get('q', '');
    $vehicles = Vehicle::where('vin', 'like', "%$q%")
        ->orWhere('brand_name', 'like', "%$q%")
        ->orWhere('model_name', 'like', "%$q%")
        ->limit(20)
        ->get();

    return response()->json(
        $vehicles->map(fn($v) => [
            'id' => $v->id,
            'text' => trim(($v->brand_name ?? '') . ' ' . ($v->model_name ?? '') . ' (' . $v->vin . ')')
        ])
    );
}

    public function destroy(Vehicle $vehicle)
    {

        session(['active_tab' => 'vehicles']);

        $vehicle->delete();
        return redirect()->back()->with('success', 'Транспортное средство удалено');
    }


public function getByClient($clientId)
{
    $vehicles = Vehicle::with(['brand', 'model'])
        ->where('client_id', $clientId)
        ->get()
        ->map(function($vehicle) {
            return [
                'id' => $vehicle->id,
                'text' => sprintf(
                    '%s %s (%s)',
                    $vehicle->brand->name ?? $vehicle->brand_name ?? '',
                    $vehicle->model->name ?? $vehicle->model_name ?? '-',
                    $vehicle->vin
                ),
            ];
        });

    return response()->json($vehicles);
}





}
