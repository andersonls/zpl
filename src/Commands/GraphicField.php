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
        if ($width <= 0) {
            $width = imagesx($im);
            $height = imagesy($im);
        } else {
            $aux = imagescale($im, $width);
            $height = imagesy($aux);
            imagedestroy($aux);
        }

        $originalWidth = imagesx($im);
        $originalHeight = imagesy($im);

        $resized = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $width, $height, $color);
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
            $rowBits = [];
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

                $auxChar = '1';
                $totalColor = $red + $green + $blue;
                if ($totalColor > $this->blackThreshold) {
                    $auxChar = '0';
                }
                $rowBits[] = $auxChar;
            }

            $bits = implode('', $rowBits);
            $bytes = str_split(str_pad($bits, ceil(strlen($bits) / 8) * 8, '0'), 8);
            $row = implode('', array_map(function ($byte) {
                return sprintf('%02X', bindec($byte));
            }, $bytes));

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
