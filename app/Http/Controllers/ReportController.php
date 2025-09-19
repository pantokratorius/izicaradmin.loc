<?php
namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    // always define these
    $startDate = $request->get('start_date') ? Carbon::parse($request->start_date) : null;
    $endDate   = $request->get('end_date') ? Carbon::parse($request->end_date) : null;
    $selectedDate  = $request->get('date') ? Carbon::parse($request->date) : Carbon::today();
    $selectedMonth = $request->get('month') ? intval($request->month) : Carbon::now()->month;
    $selectedYear  = $request->get('year') ? intval($request->year) : Carbon::now()->year;

    $results = [];

    if ($period) {
        $orderItems = OrderItem::with(['order.client','order.vehicle.client'])->get();

        if ($startDate || $endDate) {
            $orderItems = $orderItems->filter(function ($item) use ($startDate, $endDate) {
                $created = $item->order?->created_at;
                if (!$created) return false;
                if ($startDate && $created->lt($startDate)) return false;
                if ($endDate && $created->gt($endDate)) return false;
                return true;
            });
        }

        $orderItems = $orderItems->filter(function ($item) use ($period, $selectedDate, $selectedMonth, $selectedYear) {
            $created = $item->order?->created_at;
            if (!$created) return false;

            return match ($period) {
                'day'   => $created->isSameDay($selectedDate),
                'month' => $created->year === $selectedYear && $created->month === $selectedMonth,
                'year'  => $created->year === $selectedYear,
                'range' => true,
                default => false,
            };
        });

        $results = $orderItems->groupBy(function ($i) {
            return $i->order->client?->id
                ?? $i->order->vehicle?->client?->id
                ?? 0;
        })->map(function ($items) {
            $client = $items->first()->order->client
                ?? $items->first()->order->vehicle?->client;

            return [
                'client_name' => $client?->first_name . ' ' . $client?->last_name ?? '—',
                'total_sum'   => round($items->sum(fn($i) => $i->amount * $i->quantity), 2),
                'profit'      => round($items->sum(fn($i) => ($i->amount - $i->purchase_price) * $i->quantity), 2),
            ];
        })->values()->toArray();
    }

    return view('reports.index', compact(
        'results', 'period', 'startDate', 'endDate', 'selectedDate', 'selectedMonth', 'selectedYear'
    ));
}


}
