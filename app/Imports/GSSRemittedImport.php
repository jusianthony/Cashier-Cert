<?php

namespace App\Imports;

use App\Models\GSSRemitted;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class GSSRemittedImport implements ToModel, WithHeadingRow
{
    private $validated = false;

    public function model(array $row)
    {
        if (empty(array_filter($row, fn($value) => !is_null($value) && $value !== ''))) {
            return null;
        }

        if (!$this->validated) {
            $requiredHeaders = [
                'bpno', 'last_name', 'first_name', 'mi',
                'basic_monthly_salary', 'effectivity_date',
                'ps', 'gs', 'ec', 'consoloan', 'emrglyn',
                'plreg', 'gfal', 'mpl', 'mpl_lite',
                'orno', 'datepaid', 'my_covered'
            ];

            $missing = array_diff($requiredHeaders, array_map('trim', array_keys($row)));
            if (!empty($missing)) {
                throw new \Exception('Invalid GSS Excel file: missing columns - ' . implode(', ', $missing));
            }

            $this->validated = true;
        }

        $bpno                  = trim($row['bpno'] ?? '');
        $last_name             = trim($row['last_name'] ?? '');
        $first_name            = trim($row['first_name'] ?? '');
        $mi                    = trim($row['mi'] ?? '');
        $basic_monthly_salary  = $row['basic_monthly_salary'] ?? 0;
        $effectivity_date      = $this->transformDate($row['effectivity_date'] ?? '');
        $ps                    = $row['ps'] ?? 0;
        $gs                    = $row['gs'] ?? 0;
        $ec                    = $row['ec'] ?? 0;
        $consoloan             = $row['consoloan'] ?? 0;
        $emrglyn               = $row['emrglyn'] ?? 0;
        $plreg                 = $row['plreg'] ?? 0;
        $gfal                  = $row['gfal'] ?? 0;
        $mpl                   = $row['mpl'] ?? 0;
        $mpl_lite              = $row['mpl_lite'] ?? 0;
        $orno                  = trim($row['orno'] ?? '');
        $datepaid              = $this->transformDate($row['datepaid'] ?? '');
        $my_covered            = $this->transformDate($row['my_covered'] ?? '', 'F Y');

        $exists = GSSRemitted::where([
            ['bpno', $bpno],
            ['last_name', $last_name],
            ['first_name', $first_name],
            ['mi', $mi],
            ['basic_monthly_salary', $basic_monthly_salary],
            ['effectivity_date', $effectivity_date],
            ['ps', $ps],
            ['gs', $gs],
            ['ec', $ec],
            ['consoloan', $consoloan],
            ['emrglyn', $emrglyn],
            ['plreg', $plreg],
            ['gfal', $gfal],
            ['mpl', $mpl],
            ['mpl_lite', $mpl_lite],
            ['orno', $orno],
            ['datepaid', $datepaid],
            ['my_covered', $my_covered],
        ])->exists();

        return $exists ? null : new GSSRemitted([
            'bpno'                 => $bpno,
            'last_name'            => $last_name,
            'first_name'           => $first_name,
            'mi'                   => $mi,
            'basic_monthly_salary' => $basic_monthly_salary,
            'effectivity_date'     => $effectivity_date,
            'ps'                   => $ps,
            'gs'                   => $gs,
            'ec'                   => $ec,
            'consoloan'            => $consoloan,
            'emrglyn'              => $emrglyn,
            'plreg'                => $plreg,
            'gfal'                 => $gfal,
            'mpl'                  => $mpl,
            'mpl_lite'             => $mpl_lite,
            'orno'                 => $orno,
            'datepaid'             => $datepaid,
            'my_covered'           => $my_covered,
        ]);
    }

    private function transformDate($value, $format = 'Y-m-d')
    {
        try {
            if (empty($value)) {
                return null;
            }

            // Excel date (numeric)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format($format);
            }

            // Handle "June 2025"
            if ($format === 'F Y') {
                return Carbon::createFromFormat('F Y', $value)->startOfMonth()->format($format);
            }

            // Standard parsing
            return Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }
}
