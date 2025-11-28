<?php

namespace Zpl;

use function chr;
use function ord;
use function strlen;

class PdfBuilder extends AbstractBuilder
{
    /**
     * PDF driver - for example FPDF
     *
     * @var mixed
     */
    protected $pdfDriver;

    /**
     * @param string $unit - For example mm
     * @param mixed $pdfDriver PDF driver - for example FPDF
     *
     * @throws BuilderException
     */
    public function __construct(string $unit, $pdfDriver)
    {
        parent::__construct($unit);
        $this->pdfDriver = $pdfDriver;
        $this->pdfDriver->AddPage();
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::setFont
     */
    public function setFont(string $font, float $size, ?float $width = null): void
    {
        $this->pdfDriver->SetFont($font, '', $size);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::drawText
     */
    public function drawText(float $x, float $y, string $text, string $orientation = 'N', bool $invert = false): void
    {
        $this->pdfDriver->Text($x, $y, $this->_($text));
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::drawLine
     */
    public function drawLine(
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        float $thickness = 0,
        string $color = 'B',
        bool $invert = false
    ): void {
        if ($thickness !== 0.0) {
            $this->pdfDriver->SetLineWidth($thickness);
        }
        $this->pdfDriver->Line($x1, $y1, $x2, $y2);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::drawRect
     */
    public function drawRect(
        float $x,
        float $y,
        float $width,
        float $height,
        float $thickness = 0,
        string $color = 'B',
        float $round = 0,
        bool $invert = false
    ): void {
        if ($thickness !== 0.0) {
            $this->pdfDriver->SetLineWidth($thickness);
        }
        $this->pdfDriver->Rect($x, $y, $width, $height);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::drawCell
     */
    public function drawCell(
        float $width,
        float $height,
        string $text,
        bool $border = false,
        bool $ln = false,
        string $align = ''
    ): void {
        $this->pdfDriver->Cell($width, $height, $this->_($text), $border, $ln, $align);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::drawCode128
     */
    public function drawCode128(float $x, float $y, float $height, string $data, bool $printData = false, string $orientation = 'N', int $size = 0): void
    {
        $this->pdfDriver->Code128($x, $y, $height, $data);
        if ($printData === true) {
            $oldX = $this->pdfDriver->getX();
            $oldY = $this->pdfDriver->getY();
            $this->drawText($x, $y + $height * 1.3, $data);
            $this->setXY($oldX, $oldY);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws BuilderException
     *
     *@see AbstractBuilder::drawQrCode
     */
    public function drawQrCode(float $x, float $y, string $data, int $size = 10): void
    {
        throw new BuilderException('Method not yet implemented');
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawCode39()
     */
    public function drawCode39(float $x, float $y, float $height, string $data, bool $printData = false, string $orientation = 'N', int $size = 0): void
    {
        throw new BuilderException('Method not yet implemented');
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::setXY
     */
    public function setXY(float $x, float $y): void
    {
        $this->pdfDriver->setXY($x, $y);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::setX
     */
    public function setX(float $x): void
    {
        $this->pdfDriver->setX($x);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::getX
     */
    public function getX(): float
    {
        return $this->pdfDriver->getX();
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::setY
     */
    public function setY(float $y): void
    {
        $this->pdfDriver->setY($y);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractBuilder::getY
     */
    public function getY(): float
    {
        return $this->pdfDriver->getY();
    }

    /**
     * Adds a new page
     *
     * {@inheritDoc}
     *
     * @see AbstractBuilder::newPage
     */
    public function newPage(): void
    {
        $this->pdfDriver->AddPage();
    }

    /** @return mixed */
    public function getDriver()
    {
        return $this->pdfDriver;
    }

    /**
     * Converts the string to UTF-8
     */
    protected function _(string $str): string
    {
        if (extension_loaded('iconv')) {
            return iconv('UTF-8', 'ISO-8859-1', $str) ?: '';
        }
        if (extension_loaded('mbstring')) {
            return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
        }

        $len = strlen($str);

        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
            switch ($str[$i] & "\xF0") {
                case "\xC0":
                case "\xD0":
                    $c = (ord($str[$i] & "\x1F") << 6) | ord($str[++$i] & "\x3F");
                    $str[$j] = $c < 256 ? chr($c) : '?';
                    break;
                case "\xF0":
                    ++$i;
                    // no break
                case "\xE0":
                    $str[$j] = '?';
                    $i += 2;
                    break;
                default:
                    $str[$j] = $str[$i];
            }
        }

        return substr($str, 0, $j);
    }

    /**
     * Convert instance to pdf.
     */
    public function __toString(): string
    {
        return $this->pdfDriver->Output('', 'S');
    }

    /**
     * {@inheritDoc}
     *
     * @throws BuilderException
     *
     * @see AbstractBuilder::drawGraphic
     */
    public function drawGraphic(float $x, float $y, string $image, int $width = 0, bool $dithering = false): void
    {
        if (! method_exists($this->pdfDriver, 'Image')) {
            throw new BuilderException('Image method not implemented on Driver');
        }

        $this->pdfDriver->Image($image, $x, $y, $width);
    }

    /**
     * {@inheritDoc}
     *
     * @throws BuilderException
     *
     * @see AbstractBuilder::drawCircle
     */
    public function drawCircle(
        float $x,
        float $y,
        float $diameter,
        float $thickness = 0,
        string $color = 'B',
        bool $invert = false
    ): void {
        if (! method_exists($this->pdfDriver, 'Circle')) {
            throw new BuilderException('Circle method not implemented on Driver');
        }

        if ($thickness !== 0.0) {
            $this->pdfDriver->SetLineWidth($thickness);
        }

        $this->pdfDriver->Circle($x, $y, $diameter / 2);
    }
}
