<?php

namespace App\Http\Controllers;

use App\Models\CRemitted;
use App\Models\GSSRemitted;
use App\Pdf\CustomPdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{
    /**
     * Generate PAGIBIG report PDF
     */
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

    /**
     * Generate GSS report PDF
     */
    public function generateGSSReport($full_name = null)
    {
        return $this->generateReport(
            model: GSSRemitted::class,
            full_name: $full_name,
            numberField: 'bpno',
            scheme: 'GSS',
            nameField: 'employee_name'
        );
    }

    /**
     * Generate GSIS Loan report PDF with optional filters (name, date, loan types)
     */
    public function generateGSSLoanReport($full_name = null)
    {
        $startDate = request()->query('start'); // e.g. "January 2025"
        $endDate   = request()->query('end');   // e.g. "March 2025"
        $selectedLoans = request()->query('loans');

        $startCarbon = $this->parseVarcharDate($startDate, 'start');
        $endCarbon   = $this->parseVarcharDate($endDate, 'end');

        $query = GSSRemitted::query();

        if ($full_name) {
            $query->whereRaw(
                "LOWER(CONCAT(first_name, ' ', COALESCE(mi, ''), ' ', last_name)) LIKE ?",
                ['%' . strtolower(trim($full_name)) . '%']
            );
        }

        if ($startCarbon && $endCarbon) {
            $query->whereBetween(
                DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"),
                [$startCarbon->format('Y-m-d'), $endCarbon->format('Y-m-d')]
            );
        }

        $records = $query
            ->orderBy(DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"))
            ->get();

        if ($records->isEmpty() && !$full_name) {
            $latest = GSSRemitted::latest()->first();
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
        $pdf->Write(0, 'GSIS Loan Report generated on ' . strtoupper(now()->format('F j, Y')));

        if ($startDate && $endDate) {
            $pdf->SetXY(99, 35);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->Write(0, "(with date filtered: " . strtoupper($startDate . " - " . $endDate) . ")");
            $pdf->SetTextColor(0, 0, 0);
        }

        if ($records->isEmpty()) {
            $pdf->SetXY(30, 70);
            $pdf->MultiCell(0, 6, "No GSIS Loan records found for {$full_name} between {$startDate} and {$endDate}.");
        } else {
            $this->renderGSSLoanTable($pdf, $records, $selectedLoans);
        }

        $pdf->markAsLastPage();

        $fileName = $this->buildFileName('gsis_loan_report', $full_name, $startDate, $endDate);

        return response($pdf->Output('S', $fileName))
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Shared report generator for PAGIBIG and GSS reports
     */
    private function generateReport($model, $full_name, $numberField, $scheme, $nameField = null)
    {
        $startDate = request()->query('start');
        $endDate   = request()->query('end');

        $startCarbon = $this->parseVarcharDate($startDate, 'start');
        $endCarbon   = $this->parseVarcharDate($endDate, 'end');

        $query = $model::query();

        if ($full_name) {
            $query->whereRaw(
                $scheme === 'GSS'
                    ? "LOWER(CONCAT(first_name, ' ', COALESCE(mi, ''), ' ', last_name)) LIKE ?"
                    : "LOWER(CONCAT(first_name, ' ', middle_name, ' ', last_name)) LIKE ?",
                ['%' . strtolower(trim($full_name)) . '%']
            );
        }

        if ($startCarbon && $endCarbon) {
            $query->whereBetween(
                DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"),
                [$startCarbon->format('Y-m-d'), $endCarbon->format('Y-m-d')]
            );
        }

        $records = $query
            ->orderBy(DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"))
            ->get();

        if ($records->isEmpty() && !$full_name) {
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
            $this->renderCertificationHeader($pdf);

            $first = $records->first();
            $fullName = $this->determineFullName($first, $nameField);
            $accountNo = $numberField && isset($first->{$numberField}) ? $first->{$numberField} : '';
            $office = "DOH-CLCHD";

            $intro = "This is to certify that as per records of this office, the following {$scheme} contributions of {$fullName} of {$office}";
            if (!empty($accountNo)) {
                $intro .= ", with {$scheme} No. {$accountNo}";
            }
            $intro .= ", were deducted from their salary and were remitted as follows:";

            $pdf->SetXY(30, 70);
            $pdf->MultiCell(0, 5, $intro);

            $this->renderTableHeader($pdf, $scheme);
            $this->renderTableBody($pdf, $records, $scheme);
        }

        $pdf->markAsLastPage();

        $fileName = $this->buildFileName(strtolower($scheme) . "_report", $full_name, $startDate, $endDate);

        return response($pdf->Output('S', $fileName))
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Parse varchar date like "January 2025"
     */
    private function parseVarcharDate($date, $type)
    {
        if (!$date) return null;
        try {
            $carbon = Carbon::parse('01 ' . $date);
            return $type === 'start' ? $carbon->startOfMonth() : $carbon->endOfMonth();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function determineFullName($record, $nameField = null)
    {
        if ($nameField && !empty($record->{$nameField})) {
            return strtoupper($record->{$nameField});
        }

        $mi = property_exists($record, 'mi') ? $record->mi : $record->middle_name ?? '';
        return strtoupper(trim("{$record->first_name} {$mi} {$record->last_name}"));
    }

    private function renderCertificationHeader($pdf)
    {
        $pdf->SetXY(10, 45);
        $pdf->Cell(0, 20, 'C E R T I F I C A T I O N', 0, 1, 'C');
    }

    private function renderTableHeader($pdf, $scheme)
    {
        $pdf->SetFont('Helvetica', 'B');
        $pdf->SetXY(30, $pdf->GetY());

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

    private function renderTableBody($pdf, $records, $scheme)
    {
        foreach ($records as $rec) {
            if ($pdf->GetY() > 230) {
                $pdf->AddPage();
                $this->renderTableHeader($pdf, $scheme);
            }

            $pdf->SetX(30);

            $coveredDate = $rec->my_covered ?? '';
            $orNo        = $rec->orno ?? '';
            $datePaid    = $rec->datepaid ? date('M d, Y', strtotime($rec->datepaid)) : '';

            if ($scheme === 'PAG-IBIG') {
                $employee = number_format($rec->pagibig_employee ?? 0, 2);
                $employer = number_format($rec->pagibig_employer ?? 0, 2);
                $total    = number_format($rec->pagibig_total ?? 0, 2);

                $pdf->Cell(27, 6, $coveredDate, 1);
                $pdf->Cell(35, 6, $orNo, 1);
                $pdf->Cell(20, 6, $datePaid, 1);
                $pdf->Cell(30, 6, $employee, 1);
                $pdf->Cell(30, 6, $employer, 1);
                $pdf->Cell(24, 6, $total, 1);
            } else {
                $ps = number_format($rec->gs_ps ?? 0, 2);
                $gs = number_format($rec->gs_gs ?? 0, 2);
                $ec = number_format($rec->gs_ec ?? 0, 2);

                $pdf->Cell(27, 6, $coveredDate, 1);
                $pdf->Cell(20, 6, $ps, 1);
                $pdf->Cell(20, 6, $gs, 1);
                $pdf->Cell(20, 6, $ec, 1);
                $pdf->Cell(35, 6, $orNo, 1);
                $pdf->Cell(30, 6, $datePaid, 1);
            }

            $pdf->Ln();
        }
    }

    private function renderGSSLoanTable($pdf, $records, $selectedLoans = null)
    {
        $pdf->SetXY(10, 45);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(0, 20, 'G S I S   L O A N   R E P O R T', 0, 1, 'C');

        $loanColumns = [
            'Conso Loan'     => 'consoloan',
            'Emergency Loan' => 'emrglyn',
            'PL Regular'     => 'plreg',
            'GFAL'           => 'gfal',
            'MPL'            => 'mpl',
            'MPL Lite'       => 'mpl_lite',
        ];

        $selected = $selectedLoans
            ? array_map('trim', explode(',', $selectedLoans))
            : array_keys($loanColumns);

        $headers   = array_merge(['Coverage Date'], $selected, ['OR Number', 'Date Paid']);
        $colWidths = array_fill(0, count($headers), 25);

        $pdf->SetXY(10, 60);
        foreach ($headers as $i => $header) {
            $pdf->Cell($colWidths[$i], 8, $header, 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Helvetica', '', 9);

        foreach ($records as $rec) {
            if ($pdf->GetY() > 230) {
                $pdf->AddPage();
                $pdf->SetXY(10, 60);
                foreach ($headers as $i => $header) {
                    $pdf->Cell($colWidths[$i], 8, $header, 1, 0, 'C');
                }
                $pdf->Ln();
                $pdf->SetFont('Helvetica', '', 9);
            }

            $pdf->Cell($colWidths[0], 6, $rec->my_covered ?? '', 1);

            foreach ($selected as $loan) {
                $field = $loanColumns[$loan] ?? null;
                $value = $field ? number_format($rec->{$field} ?? 0, 2) : '';
                $pdf->Cell(25, 6, $value, 1);
            }

            $pdf->Cell(25, 6, $rec->orno ?? '', 1);
            $pdf->Cell(25, 6, $rec->datepaid ? date('M d, Y', strtotime($rec->datepaid)) : '', 1);

            $pdf->Ln();
        }
    }

    private function buildFileName($prefix, $full_name = null, $startDate = null, $endDate = null)
    {
        $namePart = $full_name ? str_replace(' ', '_', strtolower($full_name)) : 'all';
        $fileName = "{$prefix}_{$namePart}";

        if ($startDate && $endDate) {
            $fileName .= "_" . str_replace(' ', '_', strtolower($startDate)) . "_to_" . str_replace(' ', '_', strtolower($endDate));
        }

        return $fileName . ".pdf";
    }
}
