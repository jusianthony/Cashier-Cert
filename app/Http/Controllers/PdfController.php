<?php

namespace App\Http\Controllers;

use App\Models\CRemitted;
use App\Pdf\CustomPdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{
    public function generateGSISReport($full_name = null)
    {
        // ===== GET DATE RANGE =====
        $startDate = request()->query('start'); // e.g. "January 2025"
        $endDate   = request()->query('end');   // e.g. "March 2025"

        $startCarbon = $startDate ? Carbon::parse("1 " . $startDate)->startOfMonth() : null;
        $endCarbon   = $endDate ? Carbon::parse("1 " . $endDate)->endOfMonth() : null;

        // ===== GET EMPLOYEE RECORD(S) =====
        if ($full_name) {
            $query = CRemitted::query();

            // Use LIKE for flexible name matching
            $query->whereRaw(
                "LOWER(CONCAT(first_name, ' ', middle_name, ' ', last_name)) LIKE ?",
                ['%' . strtolower(trim($full_name)) . '%']
            );

            // Filter by covered date with correct date parsing (prepend day 01)
            if ($startCarbon && $endCarbon) {
                $query->whereBetween(
                    DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"),
                    [$startCarbon->format('Y-m-d'), $endCarbon->format('Y-m-d')]
                );
            }

            $employees = $query
                ->orderBy(DB::raw("STR_TO_DATE(CONCAT('01 ', TRIM(my_covered)), '%d %M %Y')"))
                ->get();
        } else {
            $latest = CRemitted::latest()->first();
            $employees = $latest ? collect([$latest]) : collect();
        }

        // ===== LOAD PDF TEMPLATE =====
        $templatePath = storage_path('app/public/my_template.pdf');
        if (!file_exists($templatePath)) {
            abort(404, 'PDF template not found in storage/app/public.');
        }

        $pdf = new CustomPdf();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 210);

        $pdf->SetFont('Helvetica', '');
        $pdf->SetFontSize(10);

        // ===== HEADER DATE =====
        $pdf->SetXY(24, 35);
        $pdf->Write(0, 'Report generated on ' . strtoupper(now()->format('F j, Y')));


        // ===== PERIOD COVERED =====
        if ($startDate && $endDate) {
            $pdf->SetXY(89, 35);
            $pdf->Write(0, "(with remittance dates filtered: " . strtoupper($startDate . " - " . $endDate. " ) " ));
        }

        if ($employees->isEmpty()) {
            // ===== NO RECORDS FOUND =====
            $pdf->SetXY(30, 70);
            $pdf->MultiCell(0, 6, "No records found for {$full_name} between {$startDate} and {$endDate}.");
        } else {
            // ===== INTRO TEXT =====
            $firstEmployee = $employees->first();
            $fullName = strtoupper(trim("{$firstEmployee->first_name} {$firstEmployee->middle_name} {$firstEmployee->last_name}"));
            $office = "DOH-CLCHD"; // static
            $pagibigNo = $firstEmployee->pagibig_acctno ?? '';

            $certText = "This is to certify that as per records of this office, the following PAG-IBIG contributions of {$fullName} of {$office}, with HDMF No. {$pagibigNo}, were deducted from their salary and were remitted as follows:";
            $pdf->SetXY(30, 70);
            $pdf->MultiCell(0, 5, $certText);

            // ===== TABLE HEADER =====
            $pdf->SetFont('Helvetica', 'B');
            $pdf->SetXY(30, 100);
            $pdf->Cell(27, 8, 'Covered Date', 1);
            $pdf->Cell(35, 8, 'Receipt No.', 1);
            $pdf->Cell(20, 8, 'Date Paid', 1);
            $pdf->Cell(30, 8, 'Employee Contr.', 1);
            $pdf->Cell(30, 8, 'Employer Contr.', 1);
            $pdf->Cell(24, 8, 'Total Shares', 1);
            $pdf->Ln();

            // ===== TABLE DATA =====
            $pdf->SetFont('Helvetica', '');
            foreach ($employees as $employee) {
                $pdf->SetX(30);
                $pdf->Cell(27, 8, $employee->my_covered ?? '', 1);
                $pdf->Cell(35, 8, $employee->orno ?? '', 1);
                $pdf->Cell(20, 8, $employee->date ?? '', 1);
                $pdf->Cell(30, 8, number_format($employee->employee_contribution ?? 0, 2), 1);
                $pdf->Cell(30, 8, number_format($employee->employer_contribution ?? 0, 2), 1);
                $total = ($employee->employee_contribution ?? 0) + ($employee->employer_contribution ?? 0);
                $pdf->Cell(24, 8, number_format($total, 2), 1);
                $pdf->Ln();
            }
        }

        $pdf->markAsLastPage();

        // ===== FILE NAME =====
        $fileName = "gsis_report_" . str_replace(' ', '_', strtolower($full_name));
        if ($startDate && $endDate) {
            $fileName .= "_" . str_replace(' ', '_', strtolower($startDate)) . "_to_" . str_replace(' ', '_', strtolower($endDate));
        }
        $fileName .= ".pdf";

        return response($pdf->Output('S', $fileName))
            ->header('Content-Type', 'application/pdf');
    }
}
