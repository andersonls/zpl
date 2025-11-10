<?php

namespace Zpl\Commands;

class GraphicField
{
    protected $blackThreshold = 380;

    /**
     * Creates the graphic filed (^GF) command for the given image.
     *
     * @throws Exception
     */
    public function createCommand(string $filename, int $width): string
    {
        if (is_file($filename) === false) {
            throw new Exception('Given filename "' . $filename . '" not found');
        }

        return $this->encodeImage(file_get_contents($filename), $width);
    }

    /**
     * Encodes an image to the hexadecimal ASCII format required by the ZPL ^GF command
     *
     * @param string $image The binary image data
     * @param int $width The width of the image
     * @param bool $compressData true to compress the data before returning, false otherwise
     *
     * @throws Exception
     */
    public function encodeImage(string $image, int $width, bool $compressData = true): string
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
            $height = intval($originalHeight * ($width / $originalWidth));
        }

        $resized = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $width, $height, $white);
        imagecopyresampled($resized, $im, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        imagedestroy($im);

        $im = $resized;

        $width = imagesx($im);
        $height = imagesy($im);
        $widthBytes = ceil($width / 8);

        $trueColor = imageistruecolor($im);

        $total = $widthBytes * $height;
        $lastRow = null;
        $graphic = [];
        for ($h = 0; $h < $height; $h++) {
            $row = '';
            $bitBuffer = 0;
            $bitCount = 0;
            for ($w = 0; $w < $width; $w++) {
                $rgb = imagecolorat($im, $w, $h);
                if ($trueColor === false) {
                    $rgb = imagecolorsforindex($im, $rgb);

                    $red = $rgb['red'];
                    $green = $rgb['green'];
                    $blue = $rgb['blue'];
                } else {
                    $red = ($rgb >> 16) & 0xFF;
                    $green = ($rgb >> 8) & 0xFF;
                    $blue = $rgb & 0xFF;
                    $alpha = ($rgb & 0x7F000000) >> 24;

                    if ($alpha > 0) {
                        $red = 255;
                        $green = 255;
                        $blue = 255;
                    }
                }

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
        imagedestroy($im);

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
                $count .= str_repeat('z', floor($repeat / 400));
                $repeat %= 400;
            }
            if ($repeat > 19) {
                $count .= chr(ord('f') + floor($repeat / 20));
                $repeat %= 20;
            }
            if ($repeat > 0) {
                $count .= chr(ord('F') + $repeat);
            }

            return $count . substr($original, 1, 1);
        };

        return preg_replace_callback('/(.)(\1{2,})/', $callback, preg_replace(['/0+$/', '/F+$/'], [',', '!'], $row));
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
}
