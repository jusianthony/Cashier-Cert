<?php

namespace App\Imports;

use App\Models\CRemitted;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RemittedImport implements ToModel, WithHeadingRow
{
    private $validated = false;

    public function model(array $row)
    {
        // ✅ Skip empty rows
        if (empty(array_filter($row, fn($value) => !is_null($value) && $value !== ''))) {
            return null;
        }

        // ✅ Validate required headers ONCE
        if (!$this->validated) {
            $requiredHeaders = [
                'my_covered', 'pagibig_acctno', 'employee_id',
                'last_name', 'first_name', 'middle_name',
                'employee_contribution', 'employer_contribution',
                'tin', 'birthdate', 'orno', 'date'
            ];

            $missing = array_diff($requiredHeaders, array_keys($row));
            if (!empty($missing)) {
                throw new \Exception('Invalid PAG-IBIG Excel file: missing columns - ' . implode(', ', $missing));
            }

            $this->validated = true;
        }

        $my_covered            = $this->transformDate($row['my_covered'] ?? '', 'F Y');
        $pagibig_acctno        = trim($row['pagibig_acctno'] ?? '');
        $employee_id           = trim($row['employee_id'] ?? '');
        $last_name             = trim($row['last_name'] ?? '');
        $first_name            = trim($row['first_name'] ?? '');
        $middle_name           = trim($row['middle_name'] ?? '');
        $employee_contribution = $row['employee_contribution'] ?? 0;
        $employer_contribution = $row['employer_contribution'] ?? 0;
        $tin                   = trim($row['tin'] ?? '');
        $birthdate             = $this->transformDate($row['birthdate'] ?? '');
        $orno                  = trim($row['orno'] ?? '');
        $date                  = $this->transformDate($row['date'] ?? '');

        // ✅ Prevent duplicates
        $exists = CRemitted::where([
            ['my_covered', $my_covered],
            ['pagibig_acctno', $pagibig_acctno],
            ['employee_id', $employee_id],
            ['last_name', $last_name],
            ['first_name', $first_name],
            ['middle_name', $middle_name],
            ['employee_contribution', $employee_contribution],
            ['employer_contribution', $employer_contribution],
            ['tin', $tin],
            ['birthdate', $birthdate],
            ['orno', $orno],
            ['date', $date],
        ])->exists();

        return $exists ? null : new CRemitted([
            'my_covered'            => $my_covered,
            'pagibig_acctno'        => $pagibig_acctno,
            'employee_id'           => $employee_id,
            'last_name'             => $last_name,
            'first_name'            => $first_name,
            'middle_name'           => $middle_name,
            'employee_contribution' => $employee_contribution,
            'employer_contribution' => $employer_contribution,
            'tin'                   => $tin,
            'birthdate'             => $birthdate,
            'orno'                  => $orno,
            'date'                  => $date,
        ]);
    }

    private function transformDate($value, $format = 'Y-m-d')
    {
        try {
            if (empty($value)) {
                return null;
            }
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format($format);
            }
            return Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }
}
