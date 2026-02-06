<?php
// Simple FPDF class for PDF generation
class FPDF {
    protected $page;
    protected $y;
    protected $x;
    protected $fontSize;
    protected $fontFamily;
    protected $fontStyle;
    protected $pageWidth;
    protected $pageHeight;
    protected $leftMargin;
    protected $topMargin;
    protected $lineHeight;
    protected $pages;
    protected $currentPage;
    
    function __construct($orientation='P', $unit='mm', $size='A4') {
        $this->pages = array();
        $this->currentPage = 0;
        $this->pageWidth = 210;
        $this->pageHeight = 297;
        $this->leftMargin = 10;
        $this->topMargin = 10;
        $this->x = $this->leftMargin;
        $this->y = $this->topMargin;
        $this->fontSize = 12;
        $this->fontFamily = 'Arial';
        $this->fontStyle = '';
        $this->lineHeight = 5;
    }
    
    function AddPage() {
        $this->currentPage++;
        $this->pages[$this->currentPage] = '';
        $this->y = $this->topMargin;
        $this->x = $this->leftMargin;
    }
    
    function SetFont($family, $style='', $size=12) {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        $this->fontSize = $size;
        $this->lineHeight = $size * 0.5;
    }
    
    function Cell($w, $h, $txt='', $border=0, $ln=0, $align='L') {
        $this->pages[$this->currentPage] .= $txt;
        if ($ln == 1) {
            $this->pages[$this->currentPage] .= "\n";
            $this->y += $h;
            $this->x = $this->leftMargin;
        } else {
            $this->pages[$this->currentPage] .= " ";
            $this->x += $w;
        }
    }
    
    function Ln($h=null) {
        if ($h === null) $h = $this->lineHeight;
        $this->pages[$this->currentPage] .= "\n";
        $this->y += $h;
        $this->x = $this->leftMargin;
    }
    
    function Output($dest='', $name='') {
        $content = '';
        foreach ($this->pages as $page) {
            $content .= $page . "\n\n--- Page Break ---\n\n";
        }
        
        if ($dest == 'D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo "PDF CONTENT:\n\n" . $content;
        } else {
            return $content;
        }
    }
}
?>
