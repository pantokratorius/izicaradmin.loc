<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        // $query = DB::table('clients');

        // if ($search = $request->input('search')) {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('full_name', 'like', "%$search%")
        //           ->orWhere('phone', 'like', "%$search%");
        //     });
        // }

        // $clients = $query->get();
$clients= [];
        return view('clients.index', compact('clients'));
    }
}
