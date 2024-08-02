<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once DS_INTERVIEW_PLUGIN_DIR . 'vendor/tcpdf/tcpdf.php';

class CustomTCPDF extends TCPDF {

    // Header method to add logo and user info
    public function Header() {
        // Logo
        $logo = 'https://dentstats.com/wp-content/uploads/2024/07/Dentstats-Logo-3000x3000-No-Text.png';
        $this->Image($logo, 10, 10, 50 / 3, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // User info and timestamp
        $this->SetFont('helvetica', '', 10);
        $user_info = 'User: ' . $this->user_info['username'] . ' | Generated: ' . date('Y-m-d H:i:s');
        $this->Cell(0, 15, $user_info, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

        // Move below the user info
        $this->Ln(20);
    }

    // Footer method
    public function Footer() {
        $year = date('Y');
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Copyright © ' . $year . ' DentStats.com | All Rights Reserved', 0, 0, 'L');
        $this->Cell(0, 10, 'Powered by PremiumVortex.com', 0, 0, 'R');
    }

    // Method to load data (if needed)
    public function LoadData($data) {
        return $data;
    }

    // Method to create a colored table with rounded header corners
    public function ColoredTable($header, $data) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 150, 53);
        $this->SetTextColor(255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');

        // Header with rounded top corners
        $w = array(120, 60);
        $num_headers = count($header);

        // Draw header cells with rounded top corners
        $this->RoundedRect($this->GetX(), $this->GetY(), $w[0], 7, 2, '0001', 'DF'); // Top-left corner rounded
        $this->Cell($w[0], 7, $header[0], 0, 0, 'C', false);

        $this->RoundedRect($this->GetX(), $this->GetY(), $w[1], 7, 2, '1000', 'DF'); // Top-right corner rounded
        $this->Cell($w[1], 7, $header[1], 0, 0, 'C', false);
        $this->Ln();

        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');

        // Data
        foreach ($data as $row) {
            // School column
            $this->SetFillColor(255, 255, 255); // Row background color
            $this->SetTextColor(0); // Text color
            $this->MultiCell($w[0], 13, $row['school'], 'LR', 'L', false, 0); // No fill for school cells

            // Chance column with conditional background color
            $chance = (int)$row['chance'];
            if ($chance < 50) {
                $this->SetFillColor(255, 0, 0); // Red
            } elseif ($chance <= 80) {
                $this->SetFillColor(255, 255, 0); // Yellow
            } else {
                $this->SetFillColor(0, 255, 0); // Green
            }
            $this->SetTextColor(0); // Text color
            $this->MultiCell($w[1], 13, $row['chance'] . '%', 'LR', 'C', true, 1); // Fill for chance cells
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}


?>
