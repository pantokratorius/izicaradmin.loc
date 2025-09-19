<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('ru');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'period'     => 'nullable|in:range,day,month,year',
            'date'       => 'nullable|date',
            'month'      => 'nullable|integer|min:1|max:12',
            'year'       => 'nullable|integer|min:2000|max:2100',
        ]);

        $period = $request->get('period');

        $startDate     = $request->get('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate       = $request->get('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
        $selectedDate  = $request->get('date') ? Carbon::parse($request->date) : Carbon::today();
        $selectedMonth = $request->get('month') ? intval($request->month) : Carbon::now()->month;
        $selectedYear  = $request->get('year') ? intval($request->year) : Carbon::now()->year;

        $results = [];

        if ($period) {
            $query = OrderItem::query()
    ->select([
        DB::raw('COALESCE(clients.id, vehicle_clients.id, 0) as client_id'),
        DB::raw('COALESCE(MAX(clients.first_name), MAX(vehicle_clients.first_name), "") as first_name'),
        DB::raw('COALESCE(MAX(clients.last_name), MAX(vehicle_clients.last_name), "") as last_name'),
        DB::raw('SUM(order_items.sell_price * order_items.quantity) as total_sum'),
        DB::raw('SUM((order_items.sell_price - order_items.purchase_price) * order_items.quantity) as profit')
    ])
    ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
    ->leftJoin('clients', 'orders.client_id', '=', 'clients.id')
    ->leftJoin('vehicles', 'orders.vehicle_id', '=', 'vehicles.id')
    ->leftJoin('clients as vehicle_clients', 'vehicles.client_id', '=', 'vehicle_clients.id');


            // Filter by period
            if ($period === 'range') {
                if ($startDate) $query->where('orders.created_at', '>=', $startDate);
                if ($endDate) $query->where('orders.created_at', '<=', $endDate);
            } elseif ($period === 'day') {
                $query->whereDate('orders.created_at', $selectedDate);
            } elseif ($period === 'month') {
                $query->whereYear('orders.created_at', $selectedYear)
                      ->whereMonth('orders.created_at', $selectedMonth);
            } elseif ($period === 'year') {
                $query->whereYear('orders.created_at', $selectedYear);
            }

            $results = $query->groupBy('client_id')
                             ->orderBy('total_sum', 'desc')
                             ->get()
                             ->map(fn($row) => [
                                 'client_name' => trim($row->first_name . ' ' . $row->last_name) ?: '—',
                                 'total_sum'   => round($row->total_sum, 2),
                                 'profit'      => round($row->profit, 2),
                             ])
                             ->toArray();
        }

        return view('reports.index', compact(
            'results', 'period', 'startDate', 'endDate', 'selectedDate', 'selectedMonth', 'selectedYear'
        ));
    }
}
