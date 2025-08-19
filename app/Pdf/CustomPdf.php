<?php

namespace App\Pdf;

use setasign\Fpdi\Fpdi;

class CustomPdf extends Fpdi
{
    protected $isLastPage = false;

    public function markAsLastPage()
    {
        $this->isLastPage = true;
    }

    // Override Footer
    public function Footer()
    {
        if ($this->isLastPage) {
            // Set position 30mm from bottom
            $this->SetY(-65);

            $this->SetFont('Helvetica', '', 10);

            // First line left aligned
            $this->MultiCell(0, 6, "                      This certification is being issued upon the request of the member.", 0, 'L');

            $this->Ln(8); // Add some spacing

            // Move X position to the right margin before writing "Certified Correct:"
            $this->Cell(0, 6, "                      Certified Correct:", 0, 0, 'L');
        }
    }
}
