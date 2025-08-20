<?php

namespace App\Pdf;

use setasign\Fpdi\Fpdi;
use App\Models\CSignature;

class CustomPdf extends Fpdi
{
    protected $isLastPage = false;
    protected $tplIdx; // store imported template

    public function markAsLastPage()
    {
        $this->isLastPage = true;
    }

    // Load template once
    public function setTemplate($path)
    {
        $this->setSourceFile($path);
        $this->tplIdx = $this->importPage(1);
    }

    // Always apply template on every page
    public function AddPage($orientation = '', $size = '', $rotation = 0)
    {
        parent::AddPage($orientation, $size, $rotation);

        if ($this->tplIdx) {
            $this->useTemplate($this->tplIdx, 0, 0, 210);
        }
    }

    // Override Footer
    public function Footer()
    {
        if ($this->isLastPage) {
            // Position 65mm from bottom
            $this->SetY(-65);

            $this->SetFont('Helvetica', '', 10);

            // Intro note
            $this->MultiCell(
                0,
                6,
                "                      This certification is being issued upon the request of the member.",
                0,
                'L'
            );

            $this->Ln(12); // spacing before "Certified Correct"

            // Certified Correct
            $this->Cell(0, 6, "                      Certified Correct:", 0, 1, 'L');
            $this->Ln(20); // space for signature

            // === Fetch latest signature from DB ===
            $signature = CSignature::latest()->first();

            if ($signature) {
            // Name
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetX(40); // same indent as "Certified Correct"
            $this->Cell(0, 6, $signature->full_name, 0, 1, 'L');

            // Designation
            $this->SetFont('Helvetica', '', 10);
            $this->SetX(30); // same indent
            $this->Cell(0, 6, $signature->designation, 0, 1, 'L');
        }
        }
    }
}
