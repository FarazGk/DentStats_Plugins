<?php

    if (!defined('ABSPATH')) {
        exit;
    }
    
    require_once DS_INTERVIEW_PLUGIN_DIR . 'includes/custom-tcpdf.php';
    
    // Map percentage values to descriptive words
    function mapPercentageToWord($percentage) {
        if ($percentage > 80) {
            return 'High';
        } elseif ($percentage > 50) {
            return 'Moderate';
        } elseif ($percentage > 25) {
            return 'Low';
        } else {
            return 'Very Low';
        }
    }
    
    function ds_generate_pdf($data, $filename, $suggestions) {
        ds_custom_log('Starting PDF generation.');
    
        try {
            $upload_dir = wp_upload_dir();
            $pdf_path = $upload_dir['basedir'] . '/' . $filename;
            $pdf_url = $upload_dir['baseurl'] . '/' . $filename;
    
            ds_custom_log('PDF path: ' . $pdf_path);
            ds_custom_log('PDF URL: ' . $pdf_url);
    
            // create new PDF document
            $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
            ds_custom_log('PDF object created.');
    
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('DentStats');
            $pdf->SetTitle('Evaluation Report');
            $pdf->SetSubject('Evaluation Report');
            $pdf->SetKeywords('TCPDF, PDF, report, evaluation');
    
            ds_custom_log('PDF metadata set.');
    
            // set header and footer fonts
            $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
            $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
    
            ds_custom_log('PDF fonts set.');
    
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
            ds_custom_log('PDF margins and settings configured.');
    
            // add a page
            $pdf->AddPage();
            ds_custom_log('PDF page added.');
    
            // Column titles for the table
            $header = array('School', 'Chance');
    
            // Ensure that $data is an array and not empty
            if (!is_array($data) || empty($data)) {
                ds_custom_log('No data provided for PDF generation.');
                return false;
            }
    
            // Sort the data array by 'chance' value in descending order
            usort($data, function($a, $b) {
                return $b['chance'] <=> $a['chance'];
            });
    
            // Map 'chance' percentages to words and assign colors
            foreach ($data as &$row) {
                // Ensure 'chance' is numeric
                $chance_value = floatval($row['chance']);
    
                $row['chance_word'] = mapPercentageToWord($chance_value);
    
                // Assign color based on chance value
                if ($chance_value > 80) {
                    $row['color'] = ['r' => 0, 'g' => 255, 'b' => 0]; // Green
                } elseif ($chance_value > 50) {
                    $row['color'] = ['r' => 255, 'g' => 255, 'b' => 0]; // Yellow
                } else {
                    $row['color'] = ['r' => 255, 'g' => 0, 'b' => 0]; // Red
                }
            }
            unset($row); // Break the reference
    
            // Now, group the data by color to maintain coloring while keeping the sorted order
            $grouped_data = [];
            foreach ($data as $row) {
                $color_key = implode(',', $row['color']); // Create a unique key based on color
                if (!isset($grouped_data[$color_key])) {
                    $grouped_data[$color_key] = [
                        'color' => $row['color'],
                        'rows' => []
                    ];
                }
                $grouped_data[$color_key]['rows'][] = $row;
            }
    
            // Print tables grouped by color
            foreach ($grouped_data as $group) {
                $rows = $group['rows'];
                $fillColor = $group['color'];
    
                // Prepare the data for the table
                $table_data = [];
                foreach ($rows as $row) {
                    $table_data[] = [
                        'school' => $row['school'],
                        'chance' => $row['chance_word']
                    ];
                }
    
                $pdf->ColoredTable($header, $table_data, $fillColor);
                $pdf->Ln(10); // Add some space between tables
            }
    
            ds_custom_log('PDF tables added.');
    
            // Check for page break before adding suggestions
            // Estimate the required space for suggestions
            $suggestion_height = 20; // Estimate height per suggestion including spacing
            $required_space = 20 + (count($suggestions) * $suggestion_height);
    
            if ($pdf->GetY() + $required_space > ($pdf->getPageHeight() - $pdf->getBreakMargin() - PDF_MARGIN_FOOTER)) {
                $pdf->AddPage();
            } else {
                $pdf->Ln(10); // Add some space before suggestions
            }
    
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Personalized Suggestions', 0, 1, 'L');
    
            $pdf->SetFont('helvetica', '', 10);
            foreach ($suggestions as $suggestion) {
                $pdf->MultiCell(0, 10, $suggestion['suggestion'], 0, 'L', false, 1);
                $pdf->Ln(5);
    
                if (!empty($suggestion['resource_link'])) {
                    $pdf->SetTextColor(255, 150, 53); // Set link color
                    $link_text = 'Click here to learn more';
                    $pdf->Write(0, $link_text, $suggestion['resource_link']);
                    $pdf->Ln(10); // Add some space between suggestions
                    $pdf->SetTextColor(0, 0, 0); // Reset text color
                }
            }
    
            // Output PDF document
            $pdf->Output($pdf_path, 'F');
            ds_custom_log('PDF saved to: ' . $pdf_path);
    
            if (file_exists($pdf_path)) {
                ds_custom_log('PDF file exists: ' . $pdf_path);
                return $pdf_url;
            } else {
                ds_custom_log('PDF file does not exist after saving.');
                return false;
            }
        } catch (Exception $e) {
            ds_custom_log('PDF generation failed: ' . $e->getMessage());
            return false;
        }
    }
