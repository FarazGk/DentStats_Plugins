<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once DS_INTERVIEW_PLUGIN_DIR . 'vendor/tcpdf/tcpdf.php';

class CustomTCPDF extends TCPDF {

    // Header method to add background and header content
    public function Header() {
        // Draw the background
        $this->drawBackground();

        // Logo at the top-left corner (optional)
        $logo_top = 'https://dentstats.com/wp-content/uploads/2024/08/Dentstats-Logo-3000x3000-No-Text.png';
        $this->Image($logo_top, 10, 10, 16.67, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Retrieve the current user's username using WordPress functions
        $user = wp_get_current_user();
        $username = $user->user_login;

        // Get the current time according to WordPress settings
        $generated_time = current_time('Y-m-d H:i:s');

        // User info and timestamp
        $this->SetFont('helvetica', '', 10);
        $user_info = 'User: ' . $username . ' | Generated: ' . $generated_time;
        $this->Cell(0, 15, $user_info, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

        // Move below the user info
        $this->Ln(20);
    }

    // Method to draw the background
    private function drawBackground() {
        // Set background color
        $this->SetFillColor(236, 240, 242); // #ECF0F2 in RGB
        $this->Rect(0, 0, $this->getPageWidth(), $this->getPageHeight(), 'F');
    
        // Get page dimensions
        $page_width = $this->getPageWidth();
        $page_height = $this->getPageHeight();
    
        // Logo for background pattern
        $logo = 'https://dentstats.com/wp-content/uploads/2024/08/Dentstats-Logo-512x512-1.png';
    
        // Check if SetAlpha method exists
        $setAlphaExists = method_exists($this, 'SetAlpha');
        if ($setAlphaExists) {
            // Set low opacity
            $this->SetAlpha(0.1);
        }
    
        // Calculate the number of times the image needs to be repeated
        $image_width = 50; // Width of the logo in mm
        $image_height = 50; // Height of the logo in mm
        $spacing_x = 60; // Horizontal spacing between images
        $spacing_y = 60; // Vertical spacing between images
    
        // Loop to tile the background image
        for ($y = 0; $y < $page_height; $y += $spacing_y) {
            for ($x = 0; $x < $page_width; $x += $spacing_x) {
                $this->startTransform();
                // Slightly tilt the image by 10 degrees
                $this->Rotate(10, $x + ($image_width / 2), $y + ($image_height / 2));
    
                // Place the image
                $this->Image($logo, $x, $y, $image_width, $image_height, '', '', '', false, 300, '', false, false, 0);
    
                $this->stopTransform();
            }
        }
    
        // Reset opacity if SetAlpha method exists
        if ($setAlphaExists) {
            $this->SetAlpha(1);
        }
    }

    // Footer method
    public function Footer() {
        $year = date('Y');
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Copyright © ' . $year . ' DentStats.com | All Rights Reserved', 0, 0, 'L');
        $this->Cell(0, 10, 'Powered by PremiumVortex.com', 0, 0, 'R');
    }

    // Method to create a colored table with rounded header and bottom corners
    public function ColoredTable($header, $data, $fillColor) {
        // Define some variables for heights
        $header_height = 7;
        $row_height = 13;
    
        // Column widths
        $w = array(120, 60);
    
        // Function to draw the table header
        $drawHeader = function() use ($header, $w, $header_height) {
            // Colors, line width and bold font
            $this->SetFillColor(255, 150, 53);
            $this->SetTextColor(255);
            $this->SetDrawColor(255, 255, 255); // Set draw color to white for header borders
            $this->SetLineWidth(0.3);
            $this->SetFont('', 'B');

            // Get current position
            $x = $this->GetX();
            $y = $this->GetY();

            // Draw header cells with rounded top corners
            // First cell
            $this->RoundedRect($x, $y, $w[0], $header_height, 2, '0001', 'F'); // 'F' for fill only
            $this->Cell($w[0], $header_height, $header[0], 0, 0, 'C', false);

            // Second cell
            $this->RoundedRect($x + $w[0], $y, $w[1], $header_height, 2, '1000', 'F');
            $this->Cell($w[1], $header_height, $header[1], 0, 0, 'C', false);
            $this->Ln();
        };

        // Before drawing the header, check if there is enough space
        $required_space = $header_height + $row_height;
        if ($this->GetY() + $required_space > ($this->getPageHeight() - $this->getBreakMargin())) {
            // Not enough space, add a new page
            $this->AddPage();
        }

        // Draw the initial header
        $drawHeader();

        // Reset colors and font for data rows
        $this->SetFillColor(224, 235, 255); // Background color for data rows (if needed)
        $this->SetTextColor(0);
        $this->SetFont('');
        $this->SetDrawColor(0, 0, 0); // Reset draw color to black

        // Data
        $num_rows = count($data);
        foreach ($data as $index => $row) {
            // Check for page break before drawing the row
            if ($this->GetY() + $row_height > ($this->getPageHeight() - $this->getBreakMargin())) {
                // Add a new page
                $this->AddPage();

                // Redraw the header on the new page
                $drawHeader();

                // Reset colors and font for data rows after drawing the header
                $this->SetFillColor(224, 235, 255);
                $this->SetTextColor(0);
                $this->SetFont('');
                $this->SetDrawColor(0, 0, 0);
            }

            // Determine if this is the last row
            $isLastRow = ($index == $num_rows - 1);

            // Get current position
            $x = $this->GetX();
            $y = $this->GetY();

            // First cell (School)
            // Do not set fill color or draw background rectangle
            $this->MultiCell($w[0], $row_height, $row['school'], 0, 'L', false, 0);

            // Second cell (Chance)
            $this->SetFillColor($fillColor['r'], $fillColor['g'], $fillColor['b']); // Custom fill color
            if ($isLastRow) {
                // Rounded bottom-right corner
                $this->RoundedRect($x + $w[0], $y, $w[1], $row_height, 2, '0100', 'F');
            } else {
                // Regular rectangle
                $this->Rect($x + $w[0], $y, $w[1], $row_height, 'F');
            }

            // Use the chance word directly
            $chance_word = $row['chance'];

            $this->MultiCell($w[1], $row_height, $chance_word, 0, 'C', true, 1);
        }
    }
}
