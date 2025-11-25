<?php

    namespace App\Http\Controllers;

    use App\Models\DraftOrder;

    class DraftOrderController extends Controller
    {
        public function index()
        {
            $orders = DraftOrder::with(['client', 'vehicle', 'manager'])
                ->latest()
                ->paginate(20);

            return view('draft-orders.index', compact('orders'));
        }

        public function show(DraftOrder $draftOrder)
        {
            return view('draft-orders.show', compact('draftOrder'));
        }
    }





?>