<?php

namespace Zpl;

use Zpl\Commands\GraphicField;
use Zpl\Enums\Barcode;
use Zpl\Enums\Orientation;
use Zpl\Enums\Unit;
use Zpl\Fonts\AbstractMapper;

class ZplBuilder extends AbstractBuilder
{
    /**
     * ZPL commands
     *
     * @var array<string>
     */
    protected array $commands = [];

    /**
     * Commands to be inserted before beginning of ZPL document (^XA)
     *
     * @var array<string>
     */
    protected array $preCommands = [];

    /**
     * Commands to be inserted after end of ZPL document (^XZ)
     *
     * @var array<string>
     */
    protected array $postCommands = [];

    /**
     * Resolution of the printer in DPI
     */
    protected int $resolution = 203;

    protected AbstractMapper $fontMapper;

    /**
     * The registered string macros.
     *
     * @var array<string,callable>
     */
    protected static array $macros = [];

    public const PAGE_SEPARATOR = '%PAGE_SEPARATOR%';

    public const CONTROL_CHAR_HEX_MAPPINGS = [
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
     * @param Unit $unit The unit of measurement (dots or mm)
     * @param int $resolution Resolution of the document
     */
    public function __construct(Unit $unit = Unit::DOTS, int $resolution = 203)
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
        $size = round($size * ($this->resolution * 0.014));
        $command = '^CF' . $font . ',' . $size;

        if ($width !== null) {
            $width = round($width * ($this->resolution * 0.014));
            $command .= ',' . $width;
        }

        $this->commands[] = $command;
    }

    public function setQuantity(int $quantity, int $pauseQty = 0, int $replicate = 0): void
    {
        $this->commands[] = '^PQ' . $quantity . ',' . $pauseQty . ',' . (max($replicate, 0));
    }

    /**
     * Insert an autoincrement serial number into the document.
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

    public function setHome(float $x, float $y): void
    {
        $this->commands[] = '^LH' . $this->toDots($x) . ',' . $this->toDots($y);
    }

    /**
     * Value from 0 to 36.
     */
    public function setEncoding(int $code): void
    {
        $this->commands[] = '^CI' . $code;
    }

    /**
     * @param float $value from 0 to 30 in increments of 0.1
     */
    public function setDarkness(float $value): void
    {
        $this->commands[] = '~SD' . round($value, 1);
    }

    public function setOrientation(Orientation $orientation = Orientation::NORMAL): void
    {
        $this->commands[] = '^FW' . $orientation->value;
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
    public function drawText(
        float $x,
        float $y,
        string $text,
        Orientation $orientation = Orientation::NORMAL,
        bool $invert = false
    ): void {
        $this->setOrientation($orientation);
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
        $thickness = $thickness === 0.0 ? 3 : $this->toDots($thickness);
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
        $thickness = $thickness === 0.0 ? 3 : $this->toDots($thickness);
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
     * @see \Zpl\AbstractBuilder::drawBarcode()
     */
    public function drawBarcode(
        Barcode $type,
        float $x,
        float $y,
        float $height,
        string $data,
        bool $printData = false,
        bool $labelAbove = false,
        Orientation $orientation = Orientation::NORMAL,
        int $size = 0
    ): void {
        $typeValue = $type->value;
        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        if ($size > 0 && $size <= 9) {
            $this->commands[] = '^BY' . $size;
        }
        $barcode = [
            strtoupper($typeValue),
            $orientation->value,
            $this->toDots($height),
            $printData,
            $labelAbove,
            'N',
            'A',
        ];
        if (in_array($barcode[0], [
            Barcode::CODE39->value,
            Barcode::CODE11->value,
            Barcode::ANSI->value,
            Barcode::PLESSEY->value,
        ])) {
            array_splice($barcode, 2, 0, 'N');
        } elseif ($barcode[0] === Barcode::QR->value) {
            array_splice($barcode, 2, 0, '2');
            $data = 'QA,' . $data;
            $barcode = array_slice($barcode, 0, 3);
            $barcode[] = $height;
        } elseif ($barcode[0] === Barcode::MSI->value) {
            array_splice($barcode, 2, 0, 'B');
        }
        if (in_array($barcode[0], [
            Barcode::CODE11->value,
            Barcode::INTERLEAVED2->value,
            Barcode::PLANET->value,
            Barcode::EAN8->value,
            Barcode::EAN13->value,
            Barcode::INDUSTRIAL2->value,
            Barcode::UPCA->value,
            Barcode::UPC_EAN->value,
        ])) {
            $barcode = array_slice($barcode, 0, 5);
        } elseif (in_array($barcode[0], [
            Barcode::CODE39->value,
            Barcode::CODE49->value,
            Barcode::CODE93->value,
            Barcode::CODABLOCK->value,
            Barcode::UPS->value,
            Barcode::MICROPDF417->value,
            Barcode::STANDARD2->value,
            Barcode::ANSI->value,
            Barcode::LOGMARS->value,
            Barcode::MSI->value,
            Barcode::PLESSEY->value,
        ])) {
            $barcode = array_slice($barcode, 0, 6);
        }

        $this->commands[] = $this->command($barcode);
        $this->commands[] = '^FD' . $data . '^FS';
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawCode128()
     */
    public function drawCode128(
        float $x,
        float $y,
        float $height,
        string $data,
        bool $printData = false,
        Orientation $orientation = Orientation::NORMAL,
        int $size = 0
    ): void {
        $this->drawBarcode(Barcode::CODE128, $x, $y, $height, $data, $printData, false, $orientation, $size);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawQrCode()
     */
    public function drawQrCode(float $x, float $y, string $data, int $size = 10): void
    {
        $this->drawBarcode(Barcode::QR, $x, $y, (float) $size, $data);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawCode39()
     */
    public function drawCode39(
        float $x,
        float $y,
        float $height,
        string $data,
        bool $printData = false,
        Orientation $orientation = Orientation::NORMAL,
        int $size = 0
    ): void {
        $this->drawBarcode(Barcode::CODE39, $x, $y, $height, $data, $printData, false, $orientation, $size);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zpl\AbstractBuilder::drawGraphic()
     */
    public function drawGraphic(float $x, float $y, string $image, int $width = 0, bool $dithering = false): void
    {
        $gf = new GraphicField();

        $this->commands[] = '^FO' . $this->toDots($x) . ',' . $this->toDots($y);
        $this->commands[] = $gf->createCommand($image, $this->toDots($width), $dithering);
        $this->commands[] = '^FS';
    }

    /** @param array<mixed> $parameters */
    private function command(array $parameters): string
    {
        if (count($parameters) === 1 && in_array(substr($parameters[0], 0, 1), ['^', '~'])) {
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

        return (strpos($command, '~') === 0 ? '' : '^') . $command . implode(',', $parameters);
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

    /** @param array<string|array<mixed>> $commands */
    public function setPreCommands(array $commands): void
    {
        $this->preCommands = array_map(function ($command) {
            return $this->command(is_array($command) ? $command : [$command]);
        }, $commands);
    }

    public function addPostCommand(string $command): void
    {
        $this->postCommands[] = $this->command(func_get_args());
    }

    /** @param array<string|array<mixed>> $commands */
    public function setPostCommands(array $commands): void
    {
        $this->postCommands = array_map(function ($command) {
            return $this->command(is_array($command) ? $command : [$command]);
        }, $commands);
    }

    /**
     * Handle dynamic method calls.
     *
     * @param array<mixed> $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if ($macro = (static::$macros[$method] ?? false)) {
            $macro = $macro->bindTo($this); // @phpstan-ignore-line

            return $macro(...$arguments);
        }

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments); // @phpstan-ignore-line
        }

        array_unshift($arguments, $method);
        $this->commands[] = $this->command($arguments);

        return $this;
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

    protected function toDots(float $size): int
    {
        $sizeInDots = match ($this->unit) {
            Unit::MM => $size * $this->resolution / 25.4,
            default => $size,
        };

        return intval(round($sizeInDots));
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
        $this->resolution = intval(round($resolution * 25.4));
    }

    public function getDpmm(): int
    {
        return intval(round($this->resolution / 25.4));
    }

    public function setFontMapper(Fonts\AbstractMapper $mapper): void
    {
        $this->fontMapper = $mapper;
    }

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

    /**
     * Register a custom macro.
     *
     * @param-closure-this static  $macro
     *
     * @return void
     */
    public static function macro(string $name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }
}
