<?php
namespace Avana\ExcelValidation;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportExcel implements ToCollection
{
    public function collection(Collection $rows)
    {
        return $rows; //add this line
    }
}
