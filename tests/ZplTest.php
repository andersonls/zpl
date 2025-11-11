<?php

namespace Zpl\Tests;

use PHPUnit\Framework\Attributes\Test;

class ZplTest extends TestCase
{
    #[Test]
    public function emptyZpl(): void
    {
        $this->assertEquals($this->getZpl(), "^XA\n^XZ\n");
    }

    #[Test]
    public function setEncoding(): void
    {
        $this->driver->setEncoding(28);

        $this->assertEquals($this->getZpl(), "^XA\n^CI28\n^XZ\n");
    }

    #[Test]
    public function setResolution(): void
    {
        $this->driver->setDpi(300);

        $this->assertEquals($this->driver->getDpmm(), 12);
        $this->assertEquals($this->driver->getDpi(), 300);

        $this->driver->setDpi(203);

        $this->assertEquals($this->driver->getDpmm(), 8);
        $this->assertEquals($this->driver->getDpi(), 203);
    }

    #[Test]
    public function setMargin(): void
    {
        $this->driver->setMargin(10);

        $this->assertEquals($this->driver->getMargin(), 10);
    }

    #[Test]
    public function setPageSize(): void
    {
        $this->driver->setPageSize(600, 400);

        $this->assertEquals($this->driver->getHeight(), 600);
        $this->assertEquals($this->driver->getWidth(), 400);
        //$this->assertEquals($this->getZpl(), "^XA\n^PW400^XZ\n");

        $this->driver->setHeight(500);
        $this->driver->setWidth(300);

        $this->assertEquals($this->driver->getHeight(), 500);
        $this->assertEquals($this->driver->getWidth(), 300);
    }

    #[Test]
    public function setXy(): void
    {
        $this->driver->setXY(15, 10);

        $this->assertEquals($this->driver->getX(), 15);
        $this->assertEquals($this->driver->getY(), 10);

        $this->driver->setX(20);
        $this->driver->setY(20);

        $this->assertEquals($this->driver->getX(), 20);
        $this->assertEquals($this->driver->getY(), 20);
        $this->assertEquals($this->getZpl(), "^XA\n^XZ\n");
    }

    #[Test]
    public function setFont(): void
    {
        $this->driver->setFont('0', 16);

        $this->assertEquals($this->getZpl(), "^XA\n^CF0,45.472\n^XZ\n");
    }

    #[Test]
    public function drawRect(): void
    {
        $this->driver->drawRect(5, 5, 50, 30);

        $this->assertEquals($this->getZpl(), "^XA\n^FO5,5^GB50,30,3,B,0^FS\n^XZ\n");
    }

    #[Test]
    public function drawCircle(): void
    {
        $this->driver->drawCircle(60, 5, 25);

        $this->assertEquals($this->getZpl(), "^XA\n^FO60,5^GC25,3,B^FS\n^XZ\n");
    }

    #[Test]
    public function drawCell(): void
    {
        $this->driver->drawCell(100, 10, 'Hello World', true, true, 'C');

        $this->assertEquals($this->getZpl(), "^XA\n^FO0,0^GB100,10,3,B,0^FS\n^FO10,2.5\n^FB90,7.5,0,C\n^FH^FDHello World^FS\n^XZ\n");
    }

    #[Test]
    public function drawCellWithEspecialCharaters(): void
    {
        $this->driver->drawCell(100, 10, 'E ^~_ C', true, true, 'C');

        $this->assertEquals($this->getZpl(), "^XA\n^FO0,0^GB100,10,3,B,0^FS\n^FO10,2.5\n^FB90,7.5,0,C\n^FH^FDE _5E_7E_5F C^FS\n^XZ\n");
    }

    #[Test]
    public function drawText(): void
    {
        $this->driver->drawText(5, 40, 'Hello World', 'N');

        $this->assertEquals($this->getZpl(), "^XA\n^FWN\n^FO5,40\n^FH^FDHello World^FS\n^FWN\n^XZ\n");
    }

    #[Test]
    public function drawTextWithEspecialCharaters(): void
    {
        $this->driver->drawText(5, 40, 'E ^~_ C', 'N');

        $this->assertEquals($this->getZpl(), "^XA\n^FWN\n^FO5,40\n^FH^FDE _5E_7E_5F C^FS\n^FWN\n^XZ\n");
    }

    #[Test]
    public function drawCode39(): void
    {
        $this->driver->drawCode39(0, 10, 10, '123456789', true);

        $this->assertEquals($this->getZpl(), "^XA\n^FO0,10\n^B3N,N,10,Y,N\n^FD123456789^FS\n^XZ\n");
    }

    #[Test]
    public function drawCode128(): void
    {
        $this->driver->drawCode128(5, 50, 20, 'ABC123456789', true);

        $this->assertEquals($this->getZpl(), "^XA\n^FO5,50\n^BCN,20,Y,N,N,A\n^FDABC123456789^FS\n^XZ\n");
    }

    #[Test]
    public function drawQr(): void
    {
        $this->driver->drawQrCode(50, 50, 'https://example.com/product/ABC-123', 6);

        $this->assertEquals($this->getZpl(), "^XA\n^FO50,50\n^BQN,2,6\n^FDQA,https://example.com/product/ABC-123^FS\n^XZ\n");
    }

    /* current compression is failing, depends on another PR
    #[Test]
    public function drawGraphic(): void
    {
        $this->driver->drawGraphic(10, 10, $this->getFilePath('image.png'));

        $this->assertEquals($this->getZpl(), $this->getFileContent('image_compressed.txt'));
    }

    #[Test]
    public function drawGraphicWithResize(): void
    {
        $this->driver->drawGraphic(10, 10, $this->getFilePath('image.png'), 20);

        $this->assertEquals($this->getZpl(), $this->getFileContent('image_resize_compressed.txt'));
    }*/

    #[Test]
    public function getGraphicUncompressed(): void
    {
        $graphic = new \Zpl\Commands\GraphicField();
        $zpl = str_replace(["\n", ', '], ['', ','], $graphic->encodeImage($this->getFileContent('image.png'), 0, false));

        $this->assertEquals($zpl, $this->getFileContent('image_uncompressed.txt'));
    }

    #[Test]
    public function getGraphicResizeUncompressed(): void
    {
        $graphic = new \Zpl\Commands\GraphicField();
        $zpl = str_replace(["\n", ', '], ['', ','], $graphic->encodeImage($this->getFileContent('image.png'), 20, false));

        $this->assertEquals($zpl, $this->getFileContent('image_resize_uncompressed.txt'));
    }

    #[Test]
    public function arbitraryCommand(): void
    {
        $this->driver->CF('A', 30);

        $this->assertEquals($this->getZpl(), "^XA\n^CFA,30\n^XZ\n");
    }

    #[Test]
    public function addCommand(): void
    {
        $this->driver->addCommand('^CFA,30');

        $this->assertEquals($this->getZpl(), "^XA\n^CFA,30\n^XZ\n");
    }

    #[Test]
    public function addCommandWithArgs(): void
    {
        $this->driver->addCommand('CF', 'A', 30);

        $this->assertEquals($this->getZpl(), "^XA\n^CFA,30\n^XZ\n");
    }

    #[Test]
    public function addPrecommand(): void
    {
        $this->driver->addPreCommand('^LH0,0');

        $this->assertEquals($this->getZpl(), "^LH0,0\n^XA\n^XZ\n");
    }

    #[Test]
    public function addPrecommandWithArgs(): void
    {
        $this->driver->addPreCommand('LH', 0, 0);

        $this->assertEquals($this->getZpl(), "^LH0,0\n^XA\n^XZ\n");
    }

    #[Test]
    public function setPrecommands(): void
    {
        $this->driver->setPrecommands(['^LH0,0', ['LH', 1, 1]]);

        $this->assertEquals($this->getZpl(), "^LH0,0\n^LH1,1\n^XA\n^XZ\n");
    }

    #[Test]
    public function addPostcommand(): void
    {
        $this->driver->addPostCommand('^PQ1');

        $this->assertEquals($this->getZpl(), "^XA\n^XZ\n^PQ1\n");
    }

    #[Test]
    public function addPostcommandWithArgs(): void
    {
        $this->driver->addPostCommand('PQ', 1, '', 1);

        $this->assertEquals($this->getZpl(), "^XA\n^XZ\n^PQ1,,1\n");
    }

    #[Test]
    public function setPostcommands(): void
    {
        $this->driver->setPostCommands(['^PQ1', ['PQ', 2, '', 1]]);

        $this->assertEquals($this->getZpl(), "^XA\n^XZ\n^PQ1\n^PQ2,,1\n");
    }

    #[Test]
    public function resetZpl(): void
    {
        $this->driver->addCommand('^CFA,30');
        $this->driver->addPreCommand('^LH0,0');
        $this->driver->addPostCommand('^PQ1');
        $this->driver->reset();

        $this->assertEquals($this->getZpl(), "^XA\n^XZ\n");
    }
}
