<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function clients(Request $request)
    {
        $q = $request->get('q', '');
        $clients = Client::query()
            ->where('first_name', 'like', "%$q%")
            ->orWhere('middle_name', 'like', "%$q%")
            ->orWhere('last_name', 'like', "%$q%")
            ->orWhere('phone', 'like', "%$q%")
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'text' => $c->first_name. $c->middle_name. $c->last_name . ($c->phone ? " ({$c->phone})" : '')
            ]);

        return response()->json($clients);
    }

    public function vehicles(Request $request)
    {
        $q = $request->get('q', '');
        $vehicles = Vehicle::query()
            ->where('vin', 'like', "%$q%")
            ->orWhere('brand_name', 'like', "%$q%")
            ->orWhere('model_name', 'like', "%$q%")
            ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%$q%"))
            ->orWhereHas('model', fn($m) => $m->where('name', 'like', "%$q%"))
            ->limit(20)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'text' => ($v->brand->name ?? $v->brand_name) . ($v->model->name ?? $v->model_name) . ' ' . ($v->model->name ?? $v->model_name) . ' (' . $v->vin . ')'
            ]);

        return response()->json($vehicles);
    }
}
