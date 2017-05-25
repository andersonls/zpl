<?php

namespace Zpl;

require_once 'AbstractBuilder.php';
require_once 'fonts/AbstractMapper.php';

class ZplBuilder extends AbstractBuilder
{
    /**
     * ZPL commands
     * 
     * @var array
     */
    protected $_commands = array();
    
    /**
     * Commands to be inserted before beginning of ZPL document (^XA)
     * 
     * @var array
     */
    protected $_preCommands = array();
    
    /**
     * Commands to be inserted after end of ZPL document (^XZ)
     * 
     * @var array
     */
    protected $_postCommands = array();
    
    /**
     * Resolution of the printer in DPI
     * 
     * @var int
     */
    protected $_resolution = 203;
    
    protected $_fontMapper;
    
    const PAGE_SEPARATOR = '%PAGE_SEPARATOR%';
    
    /**
     * 
     * @param string  $unit       
     * @param integer $resolution Resolution of the document
     * 
     * @throws BuilderException
     */
    public function __construct($unit = 'dots', $resolution = 203)
    {
        parent::__construct($unit);
        $this->_resolution = $resolution;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::setFont()
     */
    public function setFont ($font, $size)
    {
        $fontMapper = $this->_fontMapper;
        if (isset($fontMapper::$mapper[$font])) {
            $font = $fontMapper::$mapper[$font];
        }
        $size = $size * ($this->_resolution * 0.014);
        $this->_commands[] = '^CF' . $font . ',' . $size;
    }
    
    /**
     * Value from 0 to 36.
     * 
     * @param int $code
     */
    public function setEncoding ($code)
    {
        $this->_commands[] = '^CI' . $code;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawText()
     */
    public function drawText ($x, $y, $text, $orientation = 'N')
    {
        $this->_commands[] = '^FW' . $orientation;
        $this->_commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        $this->_commands[] = '^FD' . $text . '^FS';
        $this->_commands[] = '^FWN';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawLine()
     */
    public function drawLine ($x1, $y1, $x2, $y2, $thickness = 0)
    {
        $this->drawRect($x, $y, $x2-$x1, $y2-$y1, $thickness);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawRect()
     */
    public function drawRect ($x, $y, $width, $height, $thickness = 0, $color = 'B', $round = 0)
    {
        $thickness = $thickness === 0 ? 3 : $this->toDots($thickness);
        $this->_commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y)
                           . '^GB' . $this->toDots($width) . ',' . $this->toDots($height) . ',' . $thickness . ',' . $color . ',' . $round
                           . '^FS';
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawCell()
     */
    public function drawCell ($width, $height, $text, $border=false, $ln=false, $align='')
    {
        $x = $this->getX();
        $y = $this->getY();
        if ($border === true) {
            $this->drawRect($x, $y, $width, $height);
        }
        if ($text !== '') {
            $offsetX = 10;
            $offsetY = $this->toDots($height) / 4;
            $this->_commands[] = '^FO' . ($this->toDots($x) + $offsetX) . ',' . ($this->toDots($y) + $offsetY);
            if ($align !== '') {
                $this->_commands[] = '^FB' . ($this->toDots($width) - $offsetX) . ',' . ($this->toDots($height) - $offsetY) . ',0,' . $align;
            }
            $this->_commands[] = '^FD' . $text . '^FS';
        }
        if ($ln === true) {
            $this->setY($y + $height) ;
            $this->setX($this->getMargin());
        } else {
            $this->setX($x + $width);
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::drawCode128()
     */
    public function drawCode128 ($x, $y, $height, $data, $printData = false)
    {
        $this->_commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        $this->_commands[] = '^BCN,' . $this->toDots($height) . ',' . ($printData === true ? 'Y' : 'N');
        $this->_commands[] = '^FD' . $data . '^FS';
    }
    
    /**
     * 
     * @param string $command
     */
    public function addPreCommand ($command)
    {
        $this->_preCommands[] = $command;
    }
    
    /**
     * 
     * @param array $commands
     */
    public function setPreCommands (array $commands)
    {
        $this->_preCommands = $commands;
    }
    
    /**
     * 
     * @param string $command
     */
    public function addPostCommand ($command)
    {
        $this->_postCommands[] = $command;
    }
    
    /**
     * 
     * @param array $commands
     */
    public function setPostCommands (array $commands)
    {
        $this->_postCommands = $commands;
    }
    
    /**
     * Adds a new label
     * 
     * {@inheritDoc}
     * @see \Zpl\AbstractBuilder::newPage()
     */
    public function newPage ()
    {
        $this->_commands[] = '^XZ';
        $this->_commands[] = self::PAGE_SEPARATOR;
        $this->_commands[] = '^XA';
        $this->setY(0);
        $this->setX($this->getMargin());
    }
    
    /**
     * Converts the $size from $this->_unit to dots 
     * 
     * @param float $size
     * 
     * @return float The size in dots
     */
    protected function toDots ($size)
    {
        switch ($this->_unit) {
            case 'mm':
                //1 inch = 25.4 mm
                $sizeInDots = $size * $this->_resolution / 25.4;
                break;
            default:
                $sizeInDots = $size;
                break;
        }
        return $sizeInDots;
    }
    
    public function setFontMapper (\Zpl\Fonts\AbstractMapper $mapper)
    {
        $this->_fontMapper = $mapper;
    }
    
    /**
     * Convert instance to ZPL.
     *
     * @return string
     */
    public function toZpl ()
    {
        $preCommands = array_merge($this->_preCommands, array('^XA'));
        $postCommands = array_merge(array('^XZ'), $this->_postCommands, array(''));
        
        $zpl = implode("\n", array_merge($preCommands, $this->_commands, $postCommands));
        $zpl = str_replace(self::PAGE_SEPARATOR, implode("\n", array_merge($this->_postCommands, $this->_preCommands)), $zpl);
        return $zpl;
    }
    
    /**
     * Convert instance to string.
     *
     * @return string
     */
    public function __toString ()
    {
        return $this->toZpl();
    }
}