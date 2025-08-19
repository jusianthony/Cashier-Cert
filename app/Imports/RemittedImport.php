<?php
namespace App\Imports;

use App\Models\CRemitted;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class RemittedImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
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



        $exists = CRemitted::where('my_covered', $my_covered)
            ->where('pagibig_acctno', $pagibig_acctno)
            ->where('employee_id', $employee_id)
            ->where('last_name', $last_name)
            ->where('first_name', $first_name)
            ->where('middle_name', $middle_name)
            ->where('employee_contribution', $employee_contribution)
            ->where('employer_contribution', $employer_contribution)
            ->where('tin', $tin)
            ->where('birthdate', $birthdate)
            ->where('orno', $orno)
            ->where('date', $date)
            ->exists();

        if ($exists) {
            return null;
        }

        return new CRemitted([
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
                // Excel serial date number
                return Date::excelToDateTimeObject($value)->format($format);
            }
            // Try to parse normal date string
            return Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            // Parsing failed, return null or raw value based on preference
            return null;
        }
    }
}
