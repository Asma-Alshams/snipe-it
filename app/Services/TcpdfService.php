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
     * Generate TCPDF content for notes section and return as base64 image
     */
    public function generateNotesTcpdf($checkin_note, $checkout_note, $acceptance_note)
    {
        try {
            // Create a new TCPDF instance specifically for notes
            $notesPdf = new TCPDF('L', 'mm', [200, 50], true, 'UTF-8', false);
            
            // Set document information
            $notesPdf->SetCreator('Snipe-IT');
            $notesPdf->SetAuthor('Snipe-IT');
            $notesPdf->SetTitle('Notes Section');
            
            // Set margins to minimal for notes section
            $notesPdf->SetMargins(2, 2, 2);
            $notesPdf->SetHeaderMargin(0);
            $notesPdf->SetFooterMargin(0);
            
            // Disable auto page breaks
            $notesPdf->SetAutoPageBreak(false, 0);
            
            // Set font for Arabic text
            $notesPdf->SetFont('notonaskharabicnormal', '', 10);
            
            // Add page
            $notesPdf->AddPage();
            
            // Create HTML content for notes
            $notesHtml = '
            <table width="100%" cellpadding="1" cellspacing="0" border="1">
                <tr>
                    <td width="33%" style="padding: 5px; text-align: center; font-family: \'notonaskharabicnormal\', sans-serif; font-size: 10px;">
                        <strong style="color: grey;">ملاحظات التسليم</strong><br>
                        ' . htmlspecialchars($checkin_note ?? '', ENT_QUOTES, 'UTF-8') . '
                    </td>
                    <td width="33%" style="padding: 5px; text-align: center; font-family: \'notonaskharabicnormal\', sans-serif; font-size: 10px;">
                        <strong style="color: grey;">ملاحظات الاستلام</strong><br>
                        ' . htmlspecialchars($checkout_note ?? '', ENT_QUOTES, 'UTF-8') . '
                    </td>
                    <td width="34%" style="padding: 5px; text-align: center; font-family: \'notonaskharabicnormal\', sans-serif; font-size: 10px;">
                       <strong style="color: grey;">ملاحظة الموافقة</strong><br>
                        ' . htmlspecialchars($acceptance_note ?? '', ENT_QUOTES, 'UTF-8') . '
                    </td>
                </tr>
            </table>';
            
            // Write HTML content
            $notesPdf->writeHTML($notesHtml, true, false, true, false, '');
            
            // Get the PDF content
            $pdfContent = $notesPdf->Output('notes.pdf', 'S');
            
            // Try to convert PDF to image using system command if ImageMagick is available
            if ($this->isImageMagickAvailable()) {
                $imageData = $this->convertPdfToImageWithCommand($pdfContent);
                if ($imageData) {
                    return $imageData;
                }
            }
            
            // Try ImageMagick PHP extension
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new \Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImageBlob($pdfContent);
                    $imagick->setImageFormat('png');
                    $imagick->setImageBackgroundColor('white');
                    $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                    
                    $imageData = base64_encode($imagick->getImageBlob());
                    $imagick->clear();
                    $imagick->destroy();
                    
                    return $imageData;
                } catch (Exception $e) {
                    // ImageMagick failed, continue to fallback
                }
            }
            
            // Fallback: return null to use HTML
            return null;
            
        } catch (Exception $e) {
            // If TCPDF fails, return null to use fallback HTML
            return null;
        }
    }
    
    /**
     * Check if ImageMagick command line tool is available
     */
    private function isImageMagickAvailable()
    {
        $output = shell_exec('which convert 2>/dev/null');
        return !empty($output);
    }
    
    /**
     * Convert PDF to image using ImageMagick command line
     */
    private function convertPdfToImageWithCommand($pdfContent)
    {
        try {
            // Create temporary files
            $tempPdf = tempnam(sys_get_temp_dir(), 'notes_pdf_');
            $tempPng = tempnam(sys_get_temp_dir(), 'notes_png_') . '.png';
            
            // Write PDF content to temp file
            file_put_contents($tempPdf, $pdfContent);
            
            // Convert using ImageMagick command line
            $command = "convert -density 150 -quality 100 -background white -alpha remove {$tempPdf}[0] {$tempPng} 2>/dev/null";
            $output = shell_exec($command);
            
            if (file_exists($tempPng)) {
                $imageData = base64_encode(file_get_contents($tempPng));
                
                // Clean up temp files
                unlink($tempPdf);
                unlink($tempPng);
                
                return $imageData;
            }
            
            // Clean up temp files
            unlink($tempPdf);
            if (file_exists($tempPng)) {
                unlink($tempPng);
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Generate TCPDF content for maintenance notes section and return as base64 image
     */
    public function generateMaintenanceNotesTcpdf($maintenance_note)
    {
        try {
            // Create a new TCPDF instance specifically for maintenance notes
            $notesPdf = new TCPDF('L', 'mm', [200, 30], true, 'UTF-8', false);
            
            // Set document information
            $notesPdf->SetCreator('Snipe-IT');
            $notesPdf->SetAuthor('Snipe-IT');
            $notesPdf->SetTitle('Maintenance Notes Section');
            
            // Set margins to minimal for notes section
            $notesPdf->SetMargins(2, 2, 2);
            $notesPdf->SetHeaderMargin(0);
            $notesPdf->SetFooterMargin(0);
            
            // Disable auto page breaks
            $notesPdf->SetAutoPageBreak(false, 0);
            
            // Set font for Arabic text
            $notesPdf->SetFont('aealarabiya', '', 10);
            
            // Add page
            $notesPdf->AddPage();
            
            // Create HTML content for maintenance notes
            $notesHtml = '
            <table width="100%" cellpadding="3" cellspacing="0" border="1">
                <tr>
                    <td style="padding: 8px; text-align: center; font-family: \'aealarabiya\', sans-serif; font-size: 10px;">
                        <strong>ملاحظات الصيانة</strong><br><br>
                        ' . htmlspecialchars($maintenance_note ?? '', ENT_QUOTES, 'UTF-8') . '
                    </td>
                </tr>
            </table>';
            
            // Write HTML content
            $notesPdf->writeHTML($notesHtml, true, false, true, false, '');
            
            // Get the PDF content
            $pdfContent = $notesPdf->Output('maintenance_notes.pdf', 'S');
            
            // Try to convert PDF to image using system command if ImageMagick is available
            if ($this->isImageMagickAvailable()) {
                $imageData = $this->convertPdfToImageWithCommand($pdfContent);
                if ($imageData) {
                    return $imageData;
                }
            }
            
            // Try ImageMagick PHP extension
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new \Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImageBlob($pdfContent);
                    $imagick->setImageFormat('png');
                    $imagick->setImageBackgroundColor('white');
                    $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                    
                    $imageData = base64_encode($imagick->getImageBlob());
                    $imagick->clear();
                    $imagick->destroy();
                    
                    return $imageData;
                } catch (Exception $e) {
                    // ImageMagick failed, continue to fallback
                }
            }
            
            // Fallback: return null to use HTML
            return null;
            
        } catch (Exception $e) {
            // If TCPDF fails, return null to use fallback HTML
            return null;
        }
    }
    
    /**
     * Generate HTML content for maintenance notes section (fallback method)
     */
    public function generateMaintenanceNotesHtml($maintenance_note)
    {
        // Create HTML content for maintenance notes with TCPDF-compatible styling
        $notesHtml = '
        <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                <td style="border: 1px solid #000; padding: 5px; text-align: center; font-family: \'aealarabiya\', sans-serif; font-size: 10px;">
                    <strong>ملاحظات الصيانة</strong><br>
                    ' . htmlspecialchars($maintenance_note ?? '', ENT_QUOTES, 'UTF-8') . '
                </td>
            </tr>
        </table>';
        
        return $notesHtml;
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

    /**
     * Get the underlying TCPDF instance (for advanced usage)
     */
    public function getPdf()
    {
        return $this->pdf;
    }
}
