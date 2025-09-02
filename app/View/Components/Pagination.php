<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Pagination\LengthAwarePaginator;

class Pagination extends Component
{
    public LengthAwarePaginator $paginator;

    public function __construct($paginator)
    {
        $this->paginator = $paginator;
    }

    public function render()
    {
        return view('components.pagination');
    }
}
