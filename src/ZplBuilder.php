<?php

namespace Zpl;

use Zpl\Commands\GraphicField;

class ZplBuilder extends AbstractBuilder
{
    /**
     * ZPL commands
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Commands to be inserted before beginning of ZPL document (^XA)
     *
     * @var array
     */
    protected $preCommands = [];

    /**
     * Commands to be inserted after end of ZPL document (^XZ)
     *
     * @var array
     */
    protected $postCommands = [];

    /**
     * Resolution of the printer in DPI
     *
     * @var int
     */
    protected $resolution = 203;

    /**
     * @var Fonts\AbstractMapper
     */
    protected $fontMapper;

    const PAGE_SEPARATOR = '%PAGE_SEPARATOR%';

    const CONTROL_CHAR_HEX_MAPPINGS = [
        '_' => '_5F',
        '^' => '_5E',
        '~' => '_7E',
        '{' => '_7B',
        '}' => '_7D',
        '[' => '_5B',
        ']' => '_5D',
        '#' => '_23',
        '%' => '_25',
    ];

    /**
     * @param int $resolution Resolution of the document
     *
     * @throws BuilderException
     */
    public function __construct(string $unit = 'dots', int $resolution = 203)
    {
        parent::__construct($unit);
        $this->fontMapper = new Fonts\Generic();
        $this->resolution = $resolution;
    }

    public function setHeight(float $height): void
    {
        $this->height = $this->toDots($height);
        if ($this->height > 0) {
            $this->commands[] = '^LL' . $this->height;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::setWidth()
     */
    public function setWidth(float $width): void
    {
        $this->width = $this->toDots($width);
        if ($this->width > 0) {
            $this->commands[] = '^PW' . $this->width;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::setFont()
     */
    public function setFont(string $font, float $size, ?float $width = null): void
    {
        $fontMapper = $this->fontMapper;
        $mapper = $fontMapper::$mapper;
        if (isset($mapper[$font])) {
            $font = $mapper[$font];
        }
        $size = $size * ($this->resolution * 0.014);
        $command = '^CF' . $font . ',' . $size;

        if ($width !== null) {
            $width = $width * ($this->resolution * 0.014);
            $command .= ',' . $width;
        }

        $this->commands[] = $command;
    }

    public function setQuantity(int $quantity, int $pauseQty = 0, int $replicate = 0): void
    {
        $this->commands[] = '^PQ' . $quantity . ',' . $pauseQty . ',' . ($replicate < 0 ? 0 : $replicate);
    }

    /**
     * Insert a autoincrement serial number into the document.
     *
     * @param float $x X position in user units
     * @param float $y Y position in user units
     * @param string $start starting number, interpreted as an integer, and may have leading zeros(0001)
     * @param int $step the increment value for the serial number
     * @param bool $pad to ensure a fixed length padded with zeros based on $start arg format
     * @param bool $invert Invert the color based on the background behind the text
     */
    public function drawSerialNumber(float $x, float $y, string $start = '1', int $step = 1, bool $pad = true, bool $invert = false): void
    {
        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        if ($invert === true) {
            $this->commands[] = '^FR';
        }
        $this->commands[] = '^SN' . $start . ',' . ($step <= 0 ? 1 : $step) . ',' . ($pad ? 'Y' : 'N') . '^FS';
    }

    /**
     * Value from 0 to 36.
     */
    public function setEncoding(int $code): void
    {
        $this->commands[] = '^CI' . $code;
    }

    /**
     * @param string $orientation The text orientation. Available options:
     *                            N = normal
     *                            R = rotated 90 degrees
     *                            I = inverted 180 degrees
     *                            B = bottom-up 270 degrees, read from bottom up
     */
    public function setOrientation(string $orientation = 'N')
    {
        $this->commands[] = '^FW' . $orientation;
    }

    /**
     * If true, the entire label content will be mirrored across a horizontal axis.
     * If this command is used multiple times, the last usage will take precedence.
     */
    public function invertLabelOrientation(bool $isInvert = true): void
    {
        $this->commands[] = '^PO' . ($isInvert ? 'I' : 'N');
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawText()
     */
    public function drawText(float $x, float $y, string $text, string $orientation = 'N', bool $invert = false): void
    {
        $this->commands[] = '^FW' . $orientation;
        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        if ($invert === true) {
            $this->commands[] = '^FR';
        }
        $this->commands[] = '^FH^FD' . strtr($text, self::CONTROL_CHAR_HEX_MAPPINGS) . '^FS';
        $this->commands[] = '^FWN';
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawLine()
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
        $this->drawRect(
            $this->x,
            $this->y,
            $x2 - $x1,
            $y2 - $y1,
            $thickness,
            $color,
            0,
            $invert
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawRect()
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
        $thickness = $thickness === 0 ? 3 : $this->toDots($thickness);
        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y)
                          . ($invert === true ? '^FR' : '')
                          . '^GB' . $this->toDots($width) . ',' . $this->toDots($height) . ',' . $thickness . ',' . $color . ',' . $round
                          . '^FS';
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawCircle()
     */
    public function drawCircle(
        float $x,
        float $y,
        float $diameter,
        float $thickness = 0,
        string $color = 'B',
        bool $invert = false
    ): void {
        $thickness = $thickness === 0 ? 3 : $this->toDots($thickness);
        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y)
                          . ($invert === true ? '^FR' : '')
                          . '^GC' . $this->toDots($diameter) . ',' . $thickness . ',' . $color
                          . '^FS';
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawCell()
     */
    public function drawCell(
        float $width,
        float $height,
        string $text,
        bool $border = false,
        bool $ln = false,
        string $align = ''
    ): void {
        $x = $this->getX();
        $y = $this->getY();
        if ($border === true) {
            $this->drawRect($x, $y, $width, $height);
        }
        if ($text !== '') {
            $offsetX = 10;
            $offsetY = $this->toDots($height) / 4;
            $this->commands[] = '^FO' . ($this->toDots($x) + $offsetX) . ',' . ($this->toDots($y) + $offsetY);
            if ($align !== '') {
                $this->commands[] = '^FB' . ($this->toDots($width) - $offsetX) . ',' . ($this->toDots($height) - $offsetY) . ',0,' . $align;
            }
            $this->commands[] = '^FH^FD' . strtr($text, self::CONTROL_CHAR_HEX_MAPPINGS) . '^FS';
        }
        if ($ln === true) {
            $this->setY($y + $height);
            $this->setX($this->getMargin());
        } else {
            $this->setX($x + $width);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawCode128()
     */
    public function drawCode128(float $x, float $y, float $height, string $data, bool $printData = false, string $orientation = 'N', int $size = 0): void
    {
        $validOrientations = ['N', 'R', 'I', 'B'];
        if (in_array($orientation, $validOrientations) === false) {
            throw new \InvalidArgumentException('Valid values for orientation are: ' . implode(',', $validOrientations));
        }

        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        if ($size > 0 && $size <= 9) {
            $this->commands[] = '^BY' . $size;
        }
        $this->commands[] = '^BC' . $orientation . ',' . $this->toDots($height) . ',' . ($printData === true ? 'Y' : 'N') . ',N,N,A';
        $this->commands[] = '^FD' . $data . '^FS';
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawQrCode()
     */
    public function drawQrCode(float $x, float $y, string $data, int $size = 10): void
    {
        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        $this->commands[] = '^BQN,2,' . $size;
        $this->commands[] = '^FDQA,' . $data . '^FS';
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawCode39()
     */
    public function drawCode39(float $x, float $y, float $height, string $data, bool $printData = false, string $orientation = 'N', int $size = 0): void
    {
        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        if ($size > 0 && $size <= 9) {
            $this->commands[] = '^BY' . $size;
        }
        $this->commands[] = '^B3' . $orientation . ',N,' . $this->toDots($height) . ',' . ($printData === true ? 'Y' : 'N') . ',N';
        $this->commands[] = '^FD' . $data . '^FS';
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawGraphic()
     */
    public function drawGraphic(float $x, float $y, string $image, int $width = 0): void
    {
        $gf = new GraphicField();

        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        $this->commands[] = $gf->createCommand($image, $width);
        $this->commands[] = '^FS';
    }

    private function command(array $parameters): string
    {
        if (count($parameters) === 1 && strpos($parameters[0], '^') === 0) {
            return $parameters[0];
        }

        $command = ltrim(strtoupper(array_shift($parameters)), '^');
        if ($command === 'GF' && count($parameters) === 1) {
            $gf = new GraphicField();

            return $gf->createCommand($parameters[0], 0);
        }
        $parameters = array_map(function ($parameter) {
            return ! is_bool($parameter) ? $parameter : ($parameter ? 'Y' : 'N');
        }, $parameters);

        return '^' . $command . implode(',', $parameters);
    }

    /**
     * Adds an arbitrary command to the command queue
     */
    public function addCommand(string $command): void
    {
        $this->commands[] = $this->command(func_get_args());
    }

    public function addPreCommand(string $command): void
    {
        $this->preCommands[] = $this->command(func_get_args());
    }

    public function setPreCommands(array $commands): void
    {
        $this->preCommands = array_map(function ($command) {
            return array_map([$this, 'command'], is_array($command) ? $command : [$command]);
        }, $commands);
    }

    public function addPostCommand(string $command): void
    {
        $this->postCommands[] = $this->command(func_get_args());
    }

    public function setPostCommands(array $commands): void
    {
        $this->postCommands = array_map(function ($command) {
            return array_map([$this, 'command'], is_array($command) ? $command : [$command]);
        }, $commands);
    }

    /**
     * Handle dynamic method calls.
     *
     * @param string $method
     * @param array $arguments
     * @return void
     */
    public function __call($method, $arguments)
    {
        array_unshift($arguments, $method);
        $this->commands[] = $this->command($arguments);
    }

    /**
     * Adds a new label
     *
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::newPage()
     */
    public function newPage(): void
    {
        $this->commands[] = '^XZ';
        $this->commands[] = self::PAGE_SEPARATOR;
        $this->commands[] = '^XA';
        $this->setY(0);
        $this->setX($this->getMargin());
    }

    /**
     * Converts the $size from $this->unit to dots
     *
     *
     * @return int The size in dots
     */
    protected function toDots(float $size): int
    {
        switch ($this->unit) {
            case 'mm':
                // 1 inch = 25.4 mm
                $sizeInDots = $size * $this->resolution / 25.4;
                break;
            default:
                $sizeInDots = $size;
                break;
        }

        return round($sizeInDots);
    }
    public function setDpi(int $resolution): void
    {
        $this->resolution = $resolution;
    }

    public function getDpi(): int
    {
        return $this->resolution;
    }

    public function setDpmm(int $resolution): void
    {
        $this->resolution = round($resolution * 25.4);
    }

    public function getDpmm(): int
    {
        return round($this->resolution / 25.4);
    }

    public function setFontMapper(Fonts\AbstractMapper $mapper): void
    {
        $this->fontMapper = $mapper;
    }

    /**
     * Convert instance to ZPL.
     */
    public function toZpl(): string
    {
        $preCommands = array_merge($this->preCommands, ['^XA']);
        $postCommands = array_merge(['^XZ'], $this->postCommands, ['']);

        $zpl = implode("\n", array_merge($preCommands, $this->commands, $postCommands));
        $commands = implode("\n", array_merge($this->postCommands, $this->preCommands));

        return str_replace(self::PAGE_SEPARATOR, $commands, $zpl);
    }

    /**
     * Convert instance to string.
     */
    public function __toString(): string
    {
        return $this->toZpl();
    }

    /**
     * Reset the command queue
     */
    public function reset(): void
    {
        $this->commands = [];
        $this->preCommands = [];
        $this->postCommands = [];
    }
}
