<?php

namespace App\Http\Controllers;

use App\Models\CRemitted;
use App\Models\GSSRemitted;
use App\Pdf\CustomPdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{
    public function generatePAGIBIGReport($full_name = null)
    {
        return $this->generateReport(
            model: CRemitted::class,
            full_name: $full_name,
            numberField: 'pagibig_acctno',
            scheme: 'PAG-IBIG',
            nameField: null
        );
    }

    public function generateGSSReport($full_name = null)
    {
        return $this->generateReport(
            model: GSSRemitted::class,
            full_name: $full_name,
            numberField: 'bpno',           // assume bpno is the “account” field
            scheme: 'GSS',
            nameField: 'employee_name'
        );
    }

    private function generateReport($model, $full_name, $numberField, $scheme, $nameField = null)
    {
        $startDate = request()->query('start');
        $endDate = request()->query('end');

        // Parse the incoming “YYYY-MM” format (month input)
        $startCarbon = null;
        $endCarbon = null;
        if ($startDate) {
            try {
                $startCarbon = Carbon::createFromFormat('Y-m', $startDate)->startOfMonth();
            } catch (\Exception $e) {
                // fallback or ignore
            }
        }
        if ($endDate) {
            try {
                $endCarbon = Carbon::createFromFormat('Y-m', $endDate)->endOfMonth();
            } catch (\Exception $e) {
                // fallback or ignore
            }
        }

        $query = $model::query();

        if ($full_name) {
            if ($scheme === 'GSS') {
                $query->whereRaw(
                    "LOWER(CONCAT(first_name, ' ', COALESCE(mi, ''), ' ', last_name)) LIKE ?",
                    ['%' . strtolower(trim($full_name)) . '%']
                );
            } else {
                $query->whereRaw(
                    "LOWER(CONCAT(first_name, ' ', middle_name, ' ', last_name)) LIKE ?",
                    ['%' . strtolower(trim($full_name)) . '%']
                );
            }

            if ($startCarbon && $endCarbon) {
                $query->whereBetween(
                    // Convert your “my_covered” string to date for comparison
                    DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"),
                    [$startCarbon->format('Y-m-d'), $endCarbon->format('Y-m-d')]
                );
            }

            $records = $query
                ->orderBy(DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"))
                ->get();
        } else {
            $latest = $model::latest()->first();
            $records = $latest ? collect([$latest]) : collect();
        }

        $templatePath = storage_path('app/public/my_template.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'PDF template not found.');
        }

        $pdf = new CustomPdf();
        $pdf->setTemplate($templatePath);
        $pdf->AddPage();

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(24, 35);
        $pdf->Write(0, 'Report generated on ' . strtoupper(now()->format('F j, Y')));

        if ($startDate && $endDate) {
            $pdf->SetXY(99, 35);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->Write(0, "(with date filtered: " . strtoupper($startDate . " - " . $endDate) . ")");
            $pdf->SetTextColor(0, 0, 0);
        }

        if ($records->isEmpty()) {
            $pdf->SetXY(30, 70);
            $pdf->MultiCell(0, 6, "No records found for {$full_name} between {$startDate} and {$endDate}.");
        } else {
            $pdf->SetXY(10, 45);
            $pdf->Cell(0, 20, 'C E R T I F I C A T I O N', 0, 1, 'C');

            $first = $records->first();

            // Determine full name with fallback
            if ($nameField && !empty($first->{$nameField})) {
                $fullName = strtoupper($first->{$nameField});
            } else {
                // fallback fields (if GSS doesn’t have middle name, adjust accordingly)
                $mi = property_exists($first, 'mi') ? $first->mi : '';
                $fullName = strtoupper(trim("{$first->first_name} {$mi} {$first->last_name}"));
            }

            // Determine account / number field
            $accountNo = '';
            if ($numberField && isset($first->{$numberField})) {
                $accountNo = $first->{$numberField};
            }

            $office = "DOH-CLCHD";

            $intro = "This is to certify that as per records of this office, the following {$scheme} contributions of {$fullName} of {$office}";
            if (!empty($accountNo)) {
                $intro .= ", with {$scheme} No. {$accountNo}";
            }
            $intro .= ", were deducted from their salary and were remitted as follows:";

            $pdf->SetXY(30, 70);
            $pdf->MultiCell(0, 5, $intro);

            // Table Header
            $pdf->SetFont('Helvetica', 'B');
            $pdf->SetXY(30, 100);
            if ($scheme === 'PAG-IBIG') {
                $pdf->Cell(27, 8, 'Covered Date', 1);
                $pdf->Cell(35, 8, 'OR No.', 1);
                $pdf->Cell(20, 8, 'Date Paid', 1);
                $pdf->Cell(30, 8, 'Employee', 1);
                $pdf->Cell(30, 8, 'Employer', 1);
                $pdf->Cell(24, 8, 'Total', 1);
                $pdf->Ln();
            } else {
                $pdf->Cell(27, 8, 'Covered Date', 1);
                $pdf->Cell(20, 8, 'PS', 1);
                $pdf->Cell(20, 8, 'GS', 1);
                $pdf->Cell(20, 8, 'EC', 1);
                $pdf->Cell(35, 8, 'OR No.', 1);
                $pdf->Cell(30, 8, 'Date Paid', 1);
                $pdf->Ln();
            }

            // Table Body
            $pdf->SetFont('Helvetica', '');
            foreach ($records as $rec) {
                if ($pdf->GetY() > 230) {
                    $pdf->AddPage();
                    $pdf->SetFont('Helvetica', 'B');
                    $pdf->SetXY(30, 40);
                    if ($scheme === 'PAG-IBIG') {
                        $pdf->Cell(27, 8, 'Covered Date', 1);
                        $pdf->Cell(35, 8, 'OR No.', 1);
                        $pdf->Cell(20, 8, 'Date Paid', 1);
                        $pdf->Cell(30, 8, 'Employee', 1);
                        $pdf->Cell(30, 8, 'Employer', 1);
                        $pdf->Cell(24, 8, 'Total', 1);
                    } else {
                        $pdf->Cell(27, 8, 'Covered Date', 1);
                        $pdf->Cell(20, 8, 'PS', 1);
                        $pdf->Cell(20, 8, 'GS', 1);
                        $pdf->Cell(20, 8, 'EC', 1);
                        $pdf->Cell(35, 8, 'OR No.', 1);
                        $pdf->Cell(30, 8, 'Date Paid', 1);
                    }
                    $pdf->Ln();
                    $pdf->SetFont('Helvetica', '');
                }

                $pdf->SetX(30);
                if ($scheme === 'PAG-IBIG') {
                    $pdf->Cell(27, 8, $rec->my_covered ?? '', 1);
                    $pdf->Cell(35, 8, $rec->orno ?? '', 1);
                    $pdf->Cell(20, 8, $rec->date ?? '', 1);
                    $pdf->Cell(30, 8, number_format($rec->employee_contribution ?? 0, 2), 1);
                    $pdf->Cell(30, 8, number_format($rec->employer_contribution ?? 0, 2), 1);
                    $total = ($rec->employee_contribution ?? 0) + ($rec->employer_contribution ?? 0);
                    $pdf->Cell(24, 8, number_format($total, 2), 1);
                } else {
                    $pdf->Cell(27, 8, $rec->my_covered ?? '', 1);
                    $pdf->Cell(20, 8, number_format($rec->ps ?? 0, 2), 1);
                    $pdf->Cell(20, 8, number_format($rec->gs ?? 0, 2), 1);
                    $pdf->Cell(20, 8, number_format($rec->ec ?? 0, 2), 1);
                    $pdf->Cell(35, 8, $rec->orno ?? '', 1);
                    $pdf->Cell(30, 8, $rec->datepaid ?? '', 1);
                }

                $pdf->Ln();
            }
        }

        $pdf->markAsLastPage();

        $fileName = strtolower($scheme) . "_report_" . str_replace(' ', '_', strtolower($full_name));
        if ($startDate && $endDate) {
            $fileName .= "_" . str_replace(' ', '_', strtolower($startDate)) . "_to_" . str_replace(' ', '_', strtolower($endDate));
        }
        $fileName .= ".pdf";

        return response($pdf->Output('S', $fileName))
            ->header('Content-Type', 'application/pdf');
    }
}
