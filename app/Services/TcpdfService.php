<?php

namespace App\Services;

use TCPDF;

class TcpdfService
{
    protected $pdf;
    protected $defaultFont = 'notonaskharabicnormal';
    protected $defaultFontSize = 12;

    public function __construct()
    {
        $this->initializePdf();
    }

    /**
     * Initialize TCPDF with default settings
     */
    protected function initializePdf()
    {
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $this->pdf->SetCreator('Snipe-IT');
        $this->pdf->SetAuthor('Snipe-IT');
        $this->pdf->SetTitle('Report');
        $this->pdf->SetSubject('Generated Report');
        
        // Set default margins
        $this->pdf->SetMargins(7, 7, 7);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $this->pdf->SetAutoPageBreak(true, 10);
        
        // Set default font
        $this->pdf->SetFont($this->defaultFont, '', $this->defaultFontSize);
    }

    /**
     * Add a new page
     */
    public function addPage()
    {
        $this->pdf->AddPage();
        return $this;
    }

    /**
     * Set font
     */
    public function setFont($font, $style = '', $size = null)
    {
        $size = $size ?: $this->defaultFontSize;
        $this->pdf->SetFont($font, $style, $size);
        return $this;
    }

    /**
     * Write HTML content
     */
    public function writeHtml($html, $ln = true, $fill = false, $reseth = true, $cell = false, $align = '')
    {
        $this->pdf->writeHTML($html, $ln, $fill, $reseth, $cell, $align);
        return $this;
    }

    /**
     * Set document title
     */
    public function setTitle($title)
    {
        $this->pdf->SetTitle($title);
        return $this;
    }

    /**
     * Set document subject
     */
    public function setSubject($subject)
    {
        $this->pdf->SetSubject($subject);
        return $this;
    }


    /**
     * Generate PDF and return as string
     */
    public function output($name = 'document.pdf', $dest = 'S')
    {
        return $this->pdf->Output($name, $dest);
    }

    /**
     * Generate PDF and return as response
     */
    public function response($filename = 'document.pdf')
    {
        $content = $this->output($filename, 'S');
        
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }


    /**
     * Create a new instance for a specific report
     */
    public static function createForReport($title = 'Report', $subject = 'Generated Report')
    {
        $service = new self();
        $service->setTitle($title);
        $service->setSubject($subject);
        return $service;
    }

    /**
     * Create a new instance for a specific report with landscape orientation
     */
    public static function createForLandscapeReport($title = 'Report', $subject = 'Generated Report')
    {
        $service = new self();
        $service->setTitle($title);
        $service->setSubject($subject);
        
        return $service;
    }

    /**
     * Add a new page with landscape orientation
     */
    public function addLandscapePage()
    {
        $this->pdf->AddPage('L');
        return $this;
    }


    /**
     * Generate TCPDF content for notes section and return as base64 image
     * Currently returns null to use HTML fallback since templates use direct variables
     */
    public function generateNotesTcpdf($checkin_note, $checkout_note, $acceptance_note)
    {
        // Return null to use HTML fallback since the template uses direct variables
        return null;
    }
    

    /**
     * Generate HTML content for notes section (fallback method)
     */
    public function generateNotesHtml($checkin_note, $checkout_note, $acceptance_note)
    {
        // Create HTML content for notes with TCPDF-compatible styling
        $notesHtml = '
        <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                <td width="33%" style="border: 1px solid #000; padding: 5px; text-align: center; font-family: \'DejaVu Sans\', sans-serif; font-size: 10px;">
                    <strong>ملاحظات التسليم</strong><br>
                    ' . htmlspecialchars($checkin_note ?? '', ENT_QUOTES, 'UTF-8') . '
                </td>
                <td width="33%" style="border: 1px solid #000; padding: 5px; text-align: center; font-family: \'DejaVu Sans\', sans-serif; font-size: 10px;">
                    <strong>ملاحظات الاستلام</strong><br>
                    ' . htmlspecialchars($checkout_note ?? '', ENT_QUOTES, 'UTF-8') . '
                </td>
                <td width="34%" style="border: 1px solid #000; padding: 5px; text-align: center; font-family: \'DejaVu Sans\', sans-serif; font-size: 10px;">
                    <strong>ملاحظة الموافقة</strong><br>
                    ' . htmlspecialchars($acceptance_note ?? '', ENT_QUOTES, 'UTF-8') . '
                </td>
            </tr>
        </table>';
        
        return $notesHtml;
    }

}
