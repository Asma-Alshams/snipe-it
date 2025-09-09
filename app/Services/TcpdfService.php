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
        $this->pdf->SetMargins(15, 15, 15);
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
     * Set margins
     */
    public function setMargins($left, $top, $right = -1, $keepmargins = false)
    {
        $this->pdf->SetMargins($left, $top, $right, $keepmargins);
        return $this;
    }

    /**
     * Set header margin
     */
    public function setHeaderMargin($margin)
    {
        $this->pdf->SetHeaderMargin($margin);
        return $this;
    }

    /**
     * Set footer margin
     */
    public function setFooterMargin($margin)
    {
        $this->pdf->SetFooterMargin($margin);
        return $this;
    }

    /**
     * Set auto page break
     */
    public function setAutoPageBreak($auto, $margin = 0)
    {
        $this->pdf->SetAutoPageBreak($auto, $margin);
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
     * Get available Arabic fonts
     */
    public static function getAvailableArabicFonts()
    {
        return [
            'tajawal' => [
                'normal' => 'tajawalnormal',
                'bold' => 'tajawalb',
                'italic' => 'tajawali',
                'bold_italic' => 'tajawalbi'
            ],
            'scheherazade' => [
                'normal' => 'scheherazadenew',
                'bold' => 'scheherazadenewb'
            ],
            'markazi' => [
                'normal' => 'markazitextnormal',
                'bold' => 'markazitextb',
                'italic' => 'markazitexti',
                'bold_italic' => 'markazitextbi'
            ],
            'noto_naskh' => [
                'normal' => 'notonaskharabicnormal',
                'bold' => 'notonaskharabicb',
                'italic' => 'notonaskharabici',
                'bold_italic' => 'notonaskharabicbi'
            ],
            'almarai' => [
                'normal' => 'almarainormal',
                'bold' => 'almaraib',
                'italic' => 'almaraii',
                'bold_italic' => 'almaraibi'
            ],
            'cairo' => [
                'normal' => 'caironormal',
                'bold' => 'cairob',
                'italic' => 'cairoi',
                'bold_italic' => 'cairobi'
            ]
        ];
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
     * Get the underlying TCPDF instance (for advanced usage)
     */
    public function getPdf()
    {
        return $this->pdf;
    }
}
