<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once DS_INTERVIEW_PLUGIN_DIR . 'includes/custom-tcpdf.php';

function ds_generate_pdf($data, $filename) {
    ds_custom_log('Starting PDF generation.');

    try {
        // Sort the data by chance in descending order
        usort($data, function ($a, $b) {
            return $b['chance'] - $a['chance'];
        });

        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['basedir'] . '/' . $filename;
        $pdf_url = $upload_dir['baseurl'] . '/' . $filename;

        // create new PDF document
        $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('DentStats');
        $pdf->SetTitle('Evaluation Report');
        $pdf->SetSubject('Evaluation Report');
        $pdf->SetKeywords('TCPDF, PDF, report, evaluation');

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

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

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // add a page
        $pdf->AddPage();

        // column titles
        $header = array('School', 'Chance');

        // print colored table
        $pdf->ColoredTable($header, $data);

        // output PDF document
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

?>
