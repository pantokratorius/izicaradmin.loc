<?php

namespace App\Imports;

use App\Models\Part;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PartsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {

        return new Part([
            'name'        => $row['nazvanie'],        // column name in your Excel
            'article'     => $row['artikul'],
            'brand'       => $row['proizvoditel'],
            'price'       => $row['cena'],
            'quantity'    => $row['kolicestvo'],
        ]);
    }
}
