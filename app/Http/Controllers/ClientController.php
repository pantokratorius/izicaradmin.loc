<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    // список клиентов
    public function index(Request $request)
    {
        $search = $request->input('search');

        $clients = DB::table('clients')
            ->when($search, function ($query, $search) {
                return $query->where('full_name', 'like', "%$search%")
                             ->orWhere('phone', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('clients.index', compact('clients'));
    }

    // форма добавления
    public function create()
    {
        return view('clients.create');
    }

    // сохранение нового клиента
   public function store(Request $request)
{
    DB::table('clients')->insert([
        'full_name'      => $request->full_name,
        'phone'          => $request->phone,
        'contact_method' => $request->contact_method,
        'birth_date'     => $request->birth_date,
        'deals_count'    => 0,
        'orders_count'   => 0,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    return redirect()->route('clients.index')->with('success', 'Клиент успешно добавлен!');
}

    // форма редактирования
    public function edit($id)
    {
        $client = DB::table('clients')->where('id', $id)->first();

        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Клиент не найден');
        }

        return view('clients.edit', compact('client'));
    }

    // обновление клиента
    public function update(Request $request, $id)
{
    DB::table('clients')->where('id', $id)->update([
        'full_name'      => $request->full_name,
        'phone'          => $request->phone,
        'contact_method' => $request->contact_method,
        'birth_date'     => $request->birth_date,
        'updated_at'     => now(),
    ]);

    return redirect()->route('clients.index')->with('success', 'Данные клиента обновлены!');
}

public function destroy($id)
{
    DB::table('clients')->where('id', $id)->delete();

    return redirect()->route('clients.index')->with('success', 'Клиент удалён!');
}
}
