<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarGeneration;
use App\Models\CarSerie;
use App\Models\CarModification;

class CarController extends Controller
{
    public function index()
    {
        $brands = CarBrand::orderBy('name')->get();
        return view('cars.index', compact('brands'));
    }

    public function models($brandId)
    {
        return CarModel::where('car_brand_id', $brandId)->orderBy('name')->get();
    }

    public function generations($modelId)
    {
        return CarGeneration::where('car_model_id', $modelId)->orderBy('year_begin')->get();
    }

    public function series($generationId)
    {
        return CarSerie::where('car_generation_id', $generationId)->orderBy('name')->get();
    }

    public function modifications($serieId)
    {
        return CarModification::where('car_serie_id', $serieId)->orderBy('name')->get();
    }


}
