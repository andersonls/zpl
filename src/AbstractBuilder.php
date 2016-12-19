<?php

namespace Zpl;

abstract class AbstractBuilder
{
    /**
     * 
     * @var string
     */
    protected $_unit = 'dots';
    
    /**
     * Current position of X coordinate in user unit
     * 
     * @var float
     */
    protected $_x = 0;
    
    /**
     * Current position Y coordinate in user unit
     * 
     * @var float
     */
    protected $_y = 0;
    
    protected $_margin = 0;
    
    protected $_height;
    
    protected $_width;
    
    const UNIT_DOTS = 'dots';
    const UNIT_MM   = 'mm';
    
    /**
     * 
     * @param string  $unit       
     * @param integer $resolution Resolution of the document
     * 
     * @throws BuilderException
     */
    public function __construct($unit = 'dots')
    {
        if ($this->verifyUnit($unit) === true) {
            $this->_unit = $unit;
        } else {
            throw new BuilderException('Unit ' . $unit . ' not recognized. Please use one of the constants of the class.');
        }
    }
    
    /**
     * 
     * @param string $font The font number on the printer
     * @param float  $size The font's size in pt
     */
    public abstract function setFont ($font, $size);
    
    /**
     * Insert a text into the document.
     * 
     * @param float  $x    X position in user units
     * @param float  $y    Y position in user units
     * @param string $text Text to be inserted
     * @param string $orientation The text orientation. Available options: 
     *                            N = normal
     *                            R = rotated 90 degrees
     *                            I = inverted 180 degrees
     *                            B = bottom-up 270 degrees, read from bottom up
     */
    public abstract function drawText ($x, $y, $text, $orientation = 'N');
    
    /**
     * 
     * @param float $x1        X1 position in user units
     * @param float $y1        Y1 position in user units
     * @param float $x2        X2 position in user units
     * @param float $y2        Y2 position in user units
     * @param float $thickness Thickness in user units
     */
    public abstract function drawLine ($x1, $y1, $x2, $y2, $thickness = 0);
    
    /**
     * 
     * @param float  $x         X position in user units
     * @param float  $y         Y position in user units
     * @param float  $width     width of the rectangle in user units
     * @param float  $height    height of the rectangle in user units
     * @param float  $thickness Thickness in user units or 0 for the default thickness
     * @param string $color     'B' for black or 'W' for white
     * @param float  $round     0 (no rounding) to 8 (heaviest rounding)
     */
    public abstract function drawRect ($x, $y, $width, $height, $thickness = 0, $color = 'B', $round = 0);
    
    /**
     *
     * @param float  $width  width of the cell in user units
     * @param float  $height height of the cell in user units
     * @param string $text   Text to be drawn
     * @param bool   $border Whether the cell have a border or not  
     * @param float  $ln     Whether to advance the X, Y coordinates to the next line
     * @param string $align  Alignment of the text inside the cell (L = left, C = center, R = right, J = justified)
     */
    public abstract function drawCell ($width, $height, $text, $border=false, $ln=false, $align='');
    
    /**
     * @param float  $x      X position in user units
     * @param float  $y      Y position in user units
     * @param float  $height height of the barcode in user units
     * @param string $data   Data to draw the barcode
     * @param bool   $printData Whether to print the data or not
     */    
    public abstract function drawCode128 ($x, $y, $height, $data, $printData = false);
    
    /**
     * 
     */
    public abstract function newPage ();
    
    /**
     * Verify if the $unit is valid 
     * 
     * @param string $unit
     * 
     * @return bool true if the unit is valid, false otherwise.
     */
    protected function verifyUnit ($unit)
    {
        $r = new \ReflectionClass('\Zpl\AbstractBuilder');
        $constants = $r->getConstants();
        $key = array_search($unit, $constants);
        if (preg_match('/UNIT/', $key)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param float $x
     * @param float $y
     */
    public function setXY ($x, $y)
    {
        $this->_x = $x;
        $this->_y = $y;
    }
    
    /**
     * 
     * @param float $x
     */
    public function setX ($x)
    {
        $this->_x = $x;
    }
    
    /**
     *
     * @return float
     */
    public function getX ()
    {
        return $this->_x;
    }
    
    /**
     *
     * @param float $y
     */
    public function setY ($y)
    {
        $this->_y = $y;
    }
    
    /**
     *
     * @return float
     */
    public function getY ()
    {
        return $this->_y;
    }
    
    /**
     * 
     * @param float $margin
     */
    public function setMargin ($margin)
    {
        $this->_margin = $margin;
    }
    
    /**
     *
     * @return float
     */
    public function getMargin ()
    {
        return $this->_margin;
    }
    
    public function setHeight ($height)
    {
        $this->_height = $height;
    }
    
    public function setWidth ($width)
    {
        $this->_width = $width;
    }
    
    public function getHeight ()
    {
        return $this->_height;
    }
    
    public function getWidth ()
    {
        return $this->_width;
    }
    
    public function setPageSize ($height, $width)
    {
        $this->setHeight($height);
        $this->setWidth($width);
    }
}