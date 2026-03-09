<?php

namespace Zpl\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use UnexpectedValueException;
use Zpl\Enums\Parity;
use Zpl\PrinterStatus;

class PrinterStatusTest extends PhpUnitTestCase
{
    #[Test]
    public function createFromRawResponseStripsStxEtxAndParsesAllFields(): void
    {
        // STX + 3 lines + ETX, as a real printer would send
        $raw =
            "\x02" .
            "030,0,0,0482,000,0,0,0\r\n" .
            "0,0,0,0,0,0,0,0\r\n" .
            "0,2,128,0,005,1,003,abc,0\r\n" .
            "\x03";

        $status = PrinterStatus::createFromRawResponse($raw);

        $this->assertSame(9600, $status->getBaudRate());
        $this->assertSame(482, $status->getLabelLength());
        $this->assertSame('Tear-Off', $status->getPrintMode());
        $this->assertSame(128, $status->getPrintWidthMode());
        $this->assertSame(5, $status->getLabelsRemainingCount());
        $this->assertTrue($status->isFormatWhilePrinting());
        $this->assertSame(3, $status->getImagesInMemoryCount());
        $this->assertSame('abc', $status->getPassword());
        $this->assertFalse($status->hasStaticRam());
    }

    #[Test]
    public function createFromRawResponseWorksWithoutStxEtx(): void
    {
        $raw =
            "030,0,0,0100,000,0,0,0\r\n" .
            "0,0,0,0,0,0,0,0\r\n" .
            "0,0,0,0,000,1,000,,0\r\n";

        $status = PrinterStatus::createFromRawResponse($raw);

        $this->assertSame(100, $status->getLabelLength());
    }

    #[Test]
    public function createFromRawResponseHandlesEmptyPassword(): void
    {
        $raw =
            "\x02" .
            "030,0,0,0,000,0,0,0\r\n" .
            "0,0,0,0,0,0,0,0\r\n" .
            "0,2,0,0,000,1,000,,0\r\n" .
            "\x03";

        $status = PrinterStatus::createFromRawResponse($raw);

        $this->assertSame('', $status->getPassword());
    }

    // ---------------------------------------------------------------------------
    // Baud rate
    // ---------------------------------------------------------------------------

    /** @return array<string,array{string,int}> */
    public static function baudRateProvider(): array
    {
        // comm_settings value → expected baud rate
        // Value encodes only bits 0,1,2,8 (baud bits); all other bits = 0
        return [
            '110 baud' => ['0',   110],    // bits: 8=0,2=0,1=0,0=0 → '0000'
            '300 baud' => ['1',   300],    // bits: 8=0,2=0,1=0,0=1 → '0001'
            '600 baud' => ['2',   600],    // bits: 8=0,2=0,1=1,0=0 → '0010'
            '1200 baud' => ['3',   1200],   // bits: 8=0,2=0,1=1,0=1 → '0011'
            '2400 baud' => ['4',   2400],   // bits: 8=0,2=1,1=0,0=0 → '0100'
            '4800 baud' => ['5',   4800],   // bits: 8=0,2=1,1=0,0=1 → '0101'
            '9600 baud' => ['6',   9600],   // bits: 8=0,2=1,1=1,0=0 → '0110'
            '19200 baud' => ['7',   19200],  // bits: 8=0,2=1,1=1,0=1 → '0111'
            '28800 baud' => ['256', 28800],  // bits: 8=1,2=0,1=0,0=0 → '1000'
            '38400 baud' => ['257', 38400],  // bits: 8=1,2=0,1=0,0=1 → '1001'
            '57600 baud' => ['258', 57600],  // bits: 8=1,2=0,1=1,0=0 → '1010'
            '14400 baud' => ['259', 14400],  // bits: 8=1,2=0,1=1,0=1 → '1011'
        ];
    }

    #[Test]
    #[DataProvider('baudRateProvider')]
    public function getBaudRateReturnsCorrectRate(string $commSettings, int $expectedBaud): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $commSettings]);

        $this->assertSame($expectedBaud, $status->getBaudRate());
    }

    #[Test]
    public function getBaudRateThrowsOnUnknownCode(): void
    {
        // comm_settings = 260 → bits 8=1,2=1,1=0,0=0 → code '1100' (not in table)
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => '260']);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('1100');

        $status->getBaudRate();
    }

    // ---------------------------------------------------------------------------
    // Handshake type
    // ---------------------------------------------------------------------------

    #[Test]
    public function getHandshakeTypeReturnsXonXoff(): void
    {
        // bit7 = 0
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([7 => 0])]);

        $this->assertSame('Xon/Xoff', $status->getHandshakeType());
    }

    #[Test]
    public function getHandshakeTypeReturnsDtr(): void
    {
        // bit7 = 1 → 128
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([7 => 1])]);

        $this->assertSame('DTR', $status->getHandshakeType());
    }

    // ---------------------------------------------------------------------------
    // Serial / Parity
    // ---------------------------------------------------------------------------

    #[Test]
    public function isSerialEnabledReturnsFalseWhenDisabled(): void
    {
        // bit5 = 0
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([5 => 0])]);

        $this->assertFalse($status->isSerialEnabled());
    }

    #[Test]
    public function isSerialEnabledReturnsTrueWhenEnabled(): void
    {
        // bit5 = 1
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([5 => 1])]);

        $this->assertTrue($status->isSerialEnabled());
    }

    #[Test]
    public function getParityReturnsNullWhenSerialDisabled(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([5 => 0])]);

        $this->assertNull($status->getParity());
    }

    #[Test]
    public function getParityReturnsOddWhenBit6IsZero(): void
    {
        // bit5=1 (serial enabled), bit6=0 (odd parity)
        $status = $this->makeStatus([
            PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([5 => 1, 6 => 0]),
        ]);

        $this->assertSame(Parity::ODD, $status->getParity());
    }

    #[Test]
    public function getParityReturnsEvenWhenBit6IsOne(): void
    {
        // bit5=1 (serial enabled), bit6=1 (even parity)
        $status = $this->makeStatus([
            PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([5 => 1, 6 => 1]),
        ]);

        $this->assertSame(Parity::EVEN, $status->getParity());
    }

    // ---------------------------------------------------------------------------
    // Stop bits / Data bits
    // ---------------------------------------------------------------------------

    #[Test]
    public function getStopBitsReturnsOne(): void
    {
        // bit4 = 0
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([4 => 0])]);

        $this->assertSame(1, $status->getStopBits());
    }

    #[Test]
    public function getStopBitsReturnsTwo(): void
    {
        // bit4 = 1
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([4 => 1])]);

        $this->assertSame(2, $status->getStopBits());
    }

    #[Test]
    public function getDataBitsReturnsSeven(): void
    {
        // bit3 = 0
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([3 => 0])]);

        $this->assertSame(7, $status->getDataBits());
    }

    #[Test]
    public function getDataBitsReturnsEight(): void
    {
        // bit3 = 1
        $status = $this->makeStatus([PrinterStatus::POS_COMM_SETTINGS => $this->commSettings([3 => 1])]);

        $this->assertSame(8, $status->getDataBits());
    }

    // ---------------------------------------------------------------------------
    // Paper / Pause / Buffer flags
    // ---------------------------------------------------------------------------

    #[Test]
    public function isPaperOutReturnsFalseWhenMediaPresent(): void
    {
        $this->assertFalse($this->makeStatus()->isPaperOut());
    }

    #[Test]
    public function isPaperOutReturnsTrueWhenMediaMissing(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_PAPER_OUT => '1']);

        $this->assertTrue($status->isPaperOut());
    }

    #[Test]
    public function isPausedReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isPaused());
    }

    #[Test]
    public function isPausedReturnsTrueWhenPaused(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_PAUSED => '1']);

        $this->assertTrue($status->isPaused());
    }

    #[Test]
    public function getLabelLengthReturnsDotsValue(): void
    {
        $this->assertSame(482, $this->makeStatus()->getLabelLength());
    }

    #[Test]
    public function getFormatCountInBufferReturnsParsedInteger(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_FORMATS_IN_BUFFER => '007']);

        $this->assertSame(7, $status->getFormatCountInBuffer());
    }

    #[Test]
    public function isBufferFullReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isBufferFull());
    }

    #[Test]
    public function isBufferFullReturnsTrueWhenFull(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_BUFFER_FULL => '1']);

        $this->assertTrue($status->isBufferFull());
    }

    // ---------------------------------------------------------------------------
    // Diagnostic mode — two independent sources
    // ---------------------------------------------------------------------------

    #[Test]
    public function isDiagnosticModeActiveFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isDiagnosticModeActive());
    }

    #[Test]
    public function isDiagnosticModeActiveViaCommDiagModeField(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_COMM_DIAG_MODE => '1']);

        $this->assertTrue($status->isDiagnosticModeActive());
    }

    #[Test]
    public function isDiagnosticModeActiveViaFunctionSettingsBit5(): void
    {
        // function_settings bit5 = 1 → 32
        $status = $this->makeStatus([PrinterStatus::POS_FUNCTION_SETTINGS => '32']);

        $this->assertTrue($status->isDiagnosticModeActive());
    }

    // ---------------------------------------------------------------------------
    // Partial format / RAM / Temperature
    // ---------------------------------------------------------------------------

    #[Test]
    public function isPartialFormatInProgressReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isPartialFormatInProgress());
    }

    #[Test]
    public function isPartialFormatInProgressReturnsTrueWhenActive(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_PARTIAL_FORMAT => '1']);

        $this->assertTrue($status->isPartialFormatInProgress());
    }

    #[Test]
    public function isRamCorruptReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isRamCorrupt());
    }

    #[Test]
    public function isRamCorruptReturnsTrueWhenCorrupted(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_CORRUPT_RAM => '1']);

        $this->assertTrue($status->isRamCorrupt());
    }

    #[Test]
    public function isOverTemperatureReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isOverTemperature());
    }

    #[Test]
    public function isOverTemperatureReturnsTrueWhenHot(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_OVER_TEMP => '1']);

        $this->assertTrue($status->isOverTemperature());
    }

    #[Test]
    public function isUnderTemperatureReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isUnderTemperature());
    }

    #[Test]
    public function isUnderTemperatureReturnsTrueWhenCold(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_UNDER_TEMP => '1']);

        $this->assertTrue($status->isUnderTemperature());
    }

    // ---------------------------------------------------------------------------
    // Media type (function settings bit 7)
    // ---------------------------------------------------------------------------

    #[Test]
    public function isMediaDieCutReturnsTrueWhenBit7IsZero(): void
    {
        // function_settings bit7 = 0
        $status = $this->makeStatus([PrinterStatus::POS_FUNCTION_SETTINGS => '0']);

        $this->assertTrue($status->isMediaDieCut());
        $this->assertFalse($status->isMediaContinuous());
    }

    #[Test]
    public function isMediaContinuousReturnsTrueWhenBit7IsOne(): void
    {
        // function_settings bit7 = 1 → 128
        $status = $this->makeStatus([PrinterStatus::POS_FUNCTION_SETTINGS => '128']);

        $this->assertTrue($status->isMediaContinuous());
        $this->assertFalse($status->isMediaDieCut());
    }

    // ---------------------------------------------------------------------------
    // Head / Ribbon
    // ---------------------------------------------------------------------------

    #[Test]
    public function isHeadUpReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isHeadUp());
    }

    #[Test]
    public function isHeadUpReturnsTrueWhenOpen(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_HEAD_UP => '1']);

        $this->assertTrue($status->isHeadUp());
    }

    #[Test]
    public function isRibbonOutReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isRibbonOut());
    }

    #[Test]
    public function isRibbonOutReturnsTrueWhenEmpty(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_RIBBON_OUT => '1']);

        $this->assertTrue($status->isRibbonOut());
    }

    // ---------------------------------------------------------------------------
    // Thermal mode (function settings bit 0)
    // ---------------------------------------------------------------------------

    #[Test]
    public function isDirectThermalModeReturnsTrueWhenBit0IsZero(): void
    {
        // function_settings bit0 = 0
        $status = $this->makeStatus([PrinterStatus::POS_FUNCTION_SETTINGS => '0']);

        $this->assertTrue($status->isDirectThermalMode());
        $this->assertFalse($status->isThermalTransferMode());
    }

    #[Test]
    public function isThermalTransferModeReturnsTrueWhenBit0IsOne(): void
    {
        // function_settings bit0 = 1
        $status = $this->makeStatus([PrinterStatus::POS_FUNCTION_SETTINGS => '1']);

        $this->assertTrue($status->isThermalTransferMode());
        $this->assertFalse($status->isDirectThermalMode());
    }

    // ---------------------------------------------------------------------------
    // Print mode
    // ---------------------------------------------------------------------------

    /** @return array<string,array{string,string}> */
    public static function printModeProvider(): array
    {
        return [
            'Rewind' => ['0', 'Rewind'],
            'Peel-Off' => ['1', 'Peel-Off'],
            'Tear-Off' => ['2', 'Tear-Off'],
            'Cutter' => ['3', 'Cutter'],
            'Applicator' => ['4', 'Applicator'],
            'Delayed cut' => ['5', 'Delayed cut'],
            'Linerless Peel' => ['6', 'Linerless Peel'],
            'Linerless Rewind' => ['7', 'Linerless Rewind'],
            'Partial Cutter' => ['8', 'Partial Cutter'],
            'RFID' => ['9', 'RFID'],
            'Linerless Cut' => ['A', 'Linerless Cut'],
            'Linerless Delayed Cut' => ['B', 'Linerless Delayed Cut'],
            'Kiosk' => ['K', 'Kiosk'],
            'Stream' => ['S', 'Stream'],
        ];
    }

    #[Test]
    #[DataProvider('printModeProvider')]
    public function getPrintModeReturnsCorrectLabel(string $modeCode, string $expectedLabel): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_PRINT_MODE => $modeCode]);

        $this->assertSame($expectedLabel, $status->getPrintMode());
    }

    #[Test]
    public function getPrintModeThrowsOnUnknownCode(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_PRINT_MODE => 'X']);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('X');

        $status->getPrintMode();
    }

    // ---------------------------------------------------------------------------
    // Remaining getters
    // ---------------------------------------------------------------------------

    #[Test]
    public function getPrintWidthModeReturnsParsedInteger(): void
    {
        $this->assertSame(128, $this->makeStatus()->getPrintWidthMode());
    }

    #[Test]
    public function isLabelWaitingReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->isLabelWaiting());
    }

    #[Test]
    public function isLabelWaitingReturnsTrueWhenWaiting(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_LABEL_WAITING => '1']);

        $this->assertTrue($status->isLabelWaiting());
    }

    #[Test]
    public function getLabelsRemainingCountReturnsParsedInteger(): void
    {
        $this->assertSame(5, $this->makeStatus()->getLabelsRemainingCount());
    }

    #[Test]
    public function isFormatWhilePrintingAlwaysReturnsTrue(): void
    {
        $this->assertTrue($this->makeStatus()->isFormatWhilePrinting());
    }

    #[Test]
    public function getImagesInMemoryCountReturnsParsedInteger(): void
    {
        $this->assertSame(3, $this->makeStatus()->getImagesInMemoryCount());
    }

    #[Test]
    public function getPasswordReturnsStringValue(): void
    {
        $this->assertSame('abc', $this->makeStatus()->getPassword());
    }

    #[Test]
    public function getPasswordReturnsEmptyStringWhenNotSet(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_PASSWORD => '']);

        $this->assertSame('', $status->getPassword());
    }

    #[Test]
    public function hasStaticRamReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->makeStatus()->hasStaticRam());
    }

    #[Test]
    public function hasStaticRamReturnsTrueWhenInstalled(): void
    {
        $status = $this->makeStatus([PrinterStatus::POS_STATIC_RAM_INSTALLED => '1']);

        $this->assertTrue($status->hasStaticRam());
    }

    /**
     * Builds a PrinterStatus from a base 25-element data array, with any
     * position overrideable via $overrides (keyed by POS_* index).
     *
     * Base values represent a realistic idle printer:
     *   - 9600 baud, 8 data bits, 2 stop bits, serial disabled, Xon/Xoff
     *   - All error flags off, Tear-Off print mode, direct thermal, die-cut media
     *
     * @param array<int,mixed> $overrides
     */
    private function makeStatus(array $overrides = []): PrinterStatus
    {
        // comm_settings "030" = 30 decimal = 0b00011110
        //   bit0=0, bit1=1, bit2=1  → baud code '0110' → 9600
        //   bit3=1 → 8 data bits
        //   bit4=1 → 2 stop bits
        //   bit5=0 → serial disabled
        //   bit6=0 → odd parity (serial disabled, so getParity()=null)
        //   bit7=0 → Xon/Xoff
        //   bit8=0 → baud MSB
        $defaults = [
            0 => '030',  // COMM_SETTINGS
            1 => '0',    // PAPER_OUT
            2 => '0',    // PAUSED
            3 => '0482', // LABEL_LENGTH
            4 => '000',  // FORMATS_IN_BUFFER
            5 => '0',    // BUFFER_FULL
            6 => '0',    // COMM_DIAG_MODE
            7 => '0',    // PARTIAL_FORMAT
            8 => '0',    // (reserved)
            9 => '0',    // CORRUPT_RAM
            10 => '0',    // OVER_TEMP
            11 => '0',    // UNDER_TEMP
            12 => '0',    // FUNCTION_SETTINGS
            13 => '0',    // (reserved)
            14 => '0',    // HEAD_UP
            15 => '0',    // RIBBON_OUT
            16 => '0',    // THERMAL_TRANSFER_MODE (legacy field, present in data)
            17 => '2',    // PRINT_MODE: Tear-Off
            18 => '128',  // PRINT_WIDTH_MODE
            19 => '0',    // LABEL_WAITING
            20 => '005',  // LABELS_REMAINING
            21 => '1',    // FORMAT_WHILE_PRINTING
            22 => '003',  // IMAGES_IN_MEMORY
            23 => 'abc',  // PASSWORD
            24 => '0',    // STATIC_RAM_INSTALLED
        ];

        return new PrinterStatus(array_replace($defaults, $overrides));
    }

    /**
     * Builds a comm_settings integer string that encodes only the bits needed
     * for a specific test, keeping all other bits at 0.
     *
     * @param array<int,int> $bits e.g. [5 => 1, 6 => 1] to set bits 5 and 6
     */
    private function commSettings(array $bits): string
    {
        $value = 0;
        foreach ($bits as $bit => $on) {
            if ($on) {
                $value |= (1 << $bit);
            }
        }

        return (string) $value;
    }
}
