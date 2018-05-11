<?php

namespace Zpl;

class PdfBuilder extends AbstractBuilder
{
    /**
     * PDF driver - for example FPDF
     * 
     * @var \Sigep\Pdf\ImprovedFPDF 
     */
    protected $_pdfDriver;
    
    /**
     * 
     * @param string  $unit       
     * @param mixed $pdfDriver PDF driver - for example FPDF
     * 
     * @throws BuilderException
     */
    public function __construct($unit = 'mm', $pdfDriver)
    {
        parent::__construct($unit);
        $this->_pdfDriver = $pdfDriver;
        $this->_pdfDriver->AddPage();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::setFont()
     */
    public function setFont ($font, $size)
    {
        $this->_pdfDriver->SetFont($font,'',$size);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawText()
     */
    public function drawText ($x, $y, $text, $orientation = 'N')
    {
        $this->_pdfDriver->Text($x, $y, $this->_($text));
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawLine()
     */
    public function drawLine ($x1, $y1, $x2, $y2, $thickness = 0)
    {
        if ($thickness !== 0) {
            $this->_pdfDriver->SetLineWidth($thickness);
        }
        $this->_pdfDriver->Line($x1, $y1, $x2, $y2);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawRect()
     */
    public function drawRect ($x, $y, $width, $height, $thickness = 0, $color = 'B', $round = 0)
    {
        if ($thickness !== 0) {
            $this->_pdfDriver->SetLineWidth($thickness);
        }
        $this->_pdfDriver->Rect($x, $y, $width, $height);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawCell()
     */
    public function drawCell ($width, $height, $text, $border=false, $ln=false, $align='')
    {
        $this->_pdfDriver->Cell($width, $height, $this->_($text), $border, $ln, $align);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawCode128()
     */
    public function drawCode128 ($x, $y, $height, $data, $printData = false)
    {
        $this->_pdfDriver->Code128($x, $y, $height, $data);
        if ($printData === true) {
            $oldX = $this->_pdfDriver->x;
            $oldY = $this->_pdfDriver->y;
            $this->drawText($x, $y+$height*1.3, $data);
            $this->setXY($oldX, $oldY);
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawQrCode()
     */
    public function drawQrCode ($x, $y, $data, $size = 10)
    {
        throw new BuilderException('Method not yet implemented');
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::setXY()
     */
    public function setXY ($x, $y)
    {
        $this->_pdfDriver->x = $x;
        $this->_pdfDriver->y = $y;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::setX()
     */
    public function setX ($x)
    {
        $this->_pdfDriver->x = $x;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::getX()
     */
    public function getX ()
    {
        return $this->_pdfDriver->x;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::setY()
     */
    public function setY ($y)
    {
        $this->_pdfDriver->y = $y;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::getY()
     */
    public function getY ()
    {
        return $this->_pdfDriver->y;
    }
    
    /**
     * Adds a new page
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::newPage()
     */
    public function newPage ()
    {
        $this->_pdfDriver->AddPage();
    }
    
    public function getDriver ()
    {
        return $this->_pdfDriver;
    }
    
    /**
     * Converts the string to UTF-8
     * @param string $str
     * 
     * @return string
     */
    protected function _($str)
    {
        if (extension_loaded('iconv')) {
            return iconv('UTF-8', 'ISO-8859-1', $str);
        } else {
            return utf8_decode($str);
        }
    }
    
    /**
     * Convert instance to pdf.
     *
     * @return string
     */
    public function __toString ()
    {
        return $this->_pdfDriver->Output('', 'S');
    }

}