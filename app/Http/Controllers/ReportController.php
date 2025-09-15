<?php
namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'period'     => 'nullable|in:range,day,month,year',
            'date'       => 'nullable|date', // for day filter
            'month'      => 'nullable|integer|min:1|max:12', // for month filter
            'year'       => 'nullable|integer|min:2000|max:2100', // for month/year/year filter
        ]);

        $period = $request->get('period') ?? 'range';
        $startDate = $request->get('start_date') ? Carbon::parse($request->start_date) : null;
        $endDate   = $request->get('end_date') ? Carbon::parse($request->end_date) : null;

        $selectedDate  = $request->get('date') ? Carbon::parse($request->date) : Carbon::today();
        $selectedMonth = $request->get('month') ? intval($request->month) : Carbon::now()->month;
        $selectedYear  = $request->get('year') ? intval($request->year) : Carbon::now()->year;

        $orderItems = OrderItem::with(['order.client'])->get();

        // Filter by start/end dates
        if ($startDate || $endDate) {
            $orderItems = $orderItems->filter(function($item) use ($startDate, $endDate) {
                $created = $item->order?->created_at;
                if (!$created) return false;
                if ($startDate && $created->lt($startDate)) return false;
                if ($endDate && $created->gt($endDate)) return false;
                return true;
            });
        }

        // Filter by period
        $orderItems = $orderItems->filter(function($item) use ($period, $selectedDate, $selectedMonth, $selectedYear) {
            $created = $item->order?->created_at;
            if (!$created) return false;

            return match($period) {
                'day'   => $created->isSameDay($selectedDate),
                'month' => $created->year === $selectedYear && $created->month === $selectedMonth,
                'year'  => $created->year === $selectedYear,
                'range' => true,
                default => true,
            };
        });

        // Group always by client
        $results = $orderItems->groupBy(fn($i) => $i->order->client->id ?? 0)
            ->map(function($items) {
                $clientName = $items->first()->order->client->first_name ?? '—';
                return [
                    'client_name' => $clientName,
                    'total_sum' => round($items->sum(fn($i) => $i->amount * $i->quantity), 2),
                    'profit' => round($items->sum(fn($i) => ($i->amount - $i->purchase_price) * $i->quantity), 2),
                ];
            })->values()->toArray();

        return view('reports.index', compact(
            'results', 'startDate', 'endDate', 'period', 'selectedDate', 'selectedMonth', 'selectedYear'
        ));
    }
}
