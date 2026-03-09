<?php

namespace Zpl\Tests;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Zpl\ZplBuilder;

class TestCase extends PhpUnitTestCase
{
    /** @var ZplBuilder */
    protected $driver;

    protected function setUp(): void
    {
        $this->driver = new ZplBuilder();
    }

    protected function getZpl(): string
    {
        return $this->driver->toZpl();
    }

    protected function getFilePath(string $file): string
    {
        return __DIR__ . '/resources/' . $file;
    }

    protected function getFileRawContent(string $file): string
    {
        return file_get_contents($this->getFilePath($file));
    }

    protected function getFileContent(string $file): string
    {
        return str_replace("\r\n", "\n", file_get_contents($this->getFilePath($file)));
    }
}
