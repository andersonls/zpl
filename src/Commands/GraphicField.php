<?php

namespace Zpl\Commands;

use GdImage;

class GraphicField
{
    protected int $blackThreshold = 380;

    /**
     * Creates the graphic filed (^GF) command for the given image.
     *
     * @throws Exception
     */
    public function createCommand(string $filename, int $width, bool $dithering = false): string
    {
        if (is_file($filename) === false) {
            throw new Exception('Given filename "' . $filename . '" not found');
        }

        return $this->encodeImage(
            image: file_get_contents($filename) ?: '',
            width: $width,
            dithering: $dithering
        );
    }

    /**
     * Encodes an image to the hexadecimal ASCII format required by the ZPL ^GF command
     *
     * @param string $image The binary image data
     * @param int $width The width of the image
     * @param bool $compressData true to compress the data before returning, false otherwise
     * @param bool $dithering true to apply Floyd–Steinberg dithering algorithm
     *
     * @throws Exception
     */
    public function encodeImage(string $image, int $width, bool $compressData = true, bool $dithering = false): string
    {
        $im = imagecreatefromstring($image);
        if ($im === false) {
            throw new Exception('Image not supported');
        }

        $originalWidth = imagesx($im);
        $originalHeight = imagesy($im);
        if ($width <= 0) {
            $width = $originalWidth;
            $height = $originalHeight;
        } else {
            $height = max(1, intval($originalHeight * ($width / $originalWidth)));
        }

        $resized = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $width, $height, $white); /* @phpstan-ignore argument.type */
        imagecopyresampled($resized, $im, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        $im = $resized;
        if ($dithering) {
            $this->applyDithering($im);
        }

        $width = imagesx($im);
        $height = imagesy($im);
        $widthBytes = ceil($width / 8);

        $total = $widthBytes * $height;
        $lastRow = null;
        $graphic = [];
        for ($h = 0; $h < $height; $h++) {
            $row = '';
            $bitBuffer = 0;
            $bitCount = 0;
            for ($w = 0; $w < $width; $w++) {
                $rgb = imagecolorat($im, $w, $h);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;

                $totalColor = $red + $green + $blue;
                $bitBuffer = ($bitBuffer << 1) | ($totalColor > $this->blackThreshold ? 0 : 1);
                $bitCount++;

                if ($bitCount === 8) {
                    $row .= sprintf('%02X', $bitBuffer);
                    $bitBuffer = $bitCount = 0;
                }
            }

            if ($bitCount > 0) {
                $row .= sprintf('%02X', $bitBuffer <<= (8 - $bitCount));
            }

            $graphic[] = $compressData === true ? $this->compressRow($row, $lastRow) : $row;
            $lastRow = $row;
        }

        return '^GFA,' . $total . ',' . $total . ',' . $widthBytes . ',' . implode('', $graphic);
    }

    protected function compressRow(string $row, ?string $previousRow): string
    {
        if ($row === $previousRow) {
            return ':';
        }

        $callback = function ($matches) {
            $original = $matches[0];
            $repeat = strlen($original);
            $count = '';

            if ($repeat > 400) {
                $count .= str_repeat('z', (int) floor($repeat / 400));
                $repeat %= 400;
            }
            if ($repeat > 19) {
                $count .= chr(ord('f') + ((int) floor($repeat / 20)));
                $repeat %= 20;
            }
            if ($repeat > 0) {
                $count .= chr(ord('F') + $repeat);
            }

            return $count . substr($original, 1, 1);
        };

        return preg_replace_callback('/(.)(\1{2,})/', $callback, preg_replace(['/0+$/', '/F+$/'], [',', '!'], $row) ?: '') ?? '';
    }

    /**
     * If a pixel value is greater than the threshold it will be converted to black during the image encoding
     *
     * @param int $threshold 0 to 765
     */
    public function setBlackThreshold(int $threshold): void
    {
        $this->blackThreshold = $threshold;
    }

    protected function applyDithering(GdImage $im, int $threshold = 128): void
    {
        $w = imagesx($im);
        $h = imagesy($im);

        $gray = [];
        for ($y = 0; $y < $h; $y++) {
            $base = $y * $w;
            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($im, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray[$base + $x] = ($r * 299 + $g * 587 + $b * 114) / 1000;
            }
        }

        $fs = [[1, 0, 7 / 16], [-1, 1, 3 / 16], [0, 1, 5 / 16], [1, 1, 1 / 16]];
        for ($y = 0; $y < $h; $y++) {
            $base = $y * $w;
            for ($x = 0; $x < $w; $x++) {
                $i = $base + $x;
                $old = $gray[$i];
                $gray[$i] = $new = $old < $threshold ? 0 : 255;
                $error = $old - $new;
                foreach ($fs as $m) {
                    $nx = $x + $m[0];
                    $ny = $y + $m[1];
                    if ($nx >= 0 && $nx < $w && $ny < $h) {
                        $ni = $ny * $w + $nx;
                        $v = $gray[$ni] + $error * $m[2];
                        $gray[$ni] = $v <= 0 ? 0 : (min($v, 255));
                    }
                }
            }
        }
        for ($y = 0; $y < $h; $y++) {
            $base = $y * $w;
            for ($x = 0; $x < $w; $x++) {
                $v = (int) $gray[$base + $x];
                imagesetpixel($im, $x, $y, ($v << 16) | ($v << 8) | $v);
            }
        }
    }
}
