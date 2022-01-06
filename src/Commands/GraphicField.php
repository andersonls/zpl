<?php

namespace Zpl\Commands;

class GraphicField
{
    protected $compressionTable = array(
        1 => 'G',
        2 => 'H',
        3 => 'I',
        4 => 'J',
        5 => 'K',
        6 => 'L',
        7 => 'M',
        8 => 'N',
        9 => 'O',
        10 => 'P',
        11 => 'Q',
        12 => 'R',
        13 => 'S',
        14 => 'T',
        15 => 'U',
        16 => 'V',
        17 => 'W',
        18 => 'X',
        19 => 'Y',
        20 => 'g',
        40 => 'h',
        60 => 'i',
        80 => 'j',
        100 => 'k',
        120 => 'l',
        140 => 'm',
        160 => 'n',
        180 => 'o',
        200 => 'p',
        220 => 'q',
        240 => 'r',
        260 => 's',
        280 => 't',
        300 => 'u',
        320 => 'v',
        340 => 'w',
        360 => 'x',
        380 => 'y',
        400 => 'z',
    );

    protected $blackThreshold = 380;

    /**
     * Creates the graphic filed (^GF) command for the given image.
     *
     * @param string $filename
     * @param int $width
     *
     * @return string
     * @throws Exception
     */
    public function createCommand(string $filename, int $width) : string
    {
        if (is_file($filename) === false) {
            throw new Exception('Given filename "' . $filename . '" not found');
        }
        return $this->encodeImage(file_get_contents($filename), $width);
    }

    /**
     * Encodes an image to the hexadecimal ASCII format required by the ZPL ^GF command
     *
     * @param string $image      The binary image data
     * @param int $width         The width of the image
     * @param bool $compressData true to compress the data before returning, false otherwise
     *
     * @return string
     * @throws Exception
     */
    public function encodeImage(string $image, int $width, bool $compressData = true) : string
    {
        $im = imagecreatefromstring($image);
        if ($im === false) {
            throw new Exception('Image not supported');
        }

        $aux = imagescale($im, $width);
        $height = imagesy($aux);

        $originalWidth = imagesx($im);
        $originalHeight = imagesy($im);

        $resized = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $width, $height, $color);
        imagecopyresampled($resized, $im, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        $im = $resized;

        $width = imagesx($im);
        $height = imagesy($im);

        $auxBinaryChar =  ['0', '0', '0', '0', '0', '0', '0', '0'];
        $widthBytes = ceil($width / 8);

        $trueColor = imageistruecolor($im);

        $total = $widthBytes*$height;
        $index = 0;
        $graphic = '';
        for ($h = 0; $h < $height; $h++) {
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

                $auxBinaryChar[$index] = $auxChar;
                $index++;
                if ($index === 8 || $w === ($width-1)) {
                    $graphic .= $this->fourByteBinary(implode($auxBinaryChar));
                    $auxBinaryChar =  ['0', '0', '0', '0', '0', '0', '0', '0'];
                    $index = 0;
                }
            }
            $graphic .= "\n";
        }

        $data = $compressData === true ? $this->compressData($graphic, $widthBytes) : $graphic;

        return '^GFA,' . $total . ',' . $total . ',' . $widthBytes . ', ' . $data;
    }

    /**
     * @param string $binaryStr
     *
     * @return string
     */
    protected function fourByteBinary(string $binaryStr) : string
    {
        $decimal = bindec($binaryStr);
        return str_pad(strtoupper(dechex($decimal)), 2, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function compressData(string $data) : string
    {
        $compressedData = '';
        $line = '';
        $previousLine = '';
        $counter = 1;
        $aux = $data[0];
        $firstChar = false;
        for ($i = 1; $i < strlen($data); $i++) {
            $char = $data[$i];
            if ($firstChar) {
                $aux = $char;
                $firstChar = false;
                continue;
            }
            if ($char === "\n") {
                if ($aux === '0') {
                    $line .= ',';
                } elseif ($aux === 'F') {
                    $line .= '!';
                } elseif ($counter > 20) {
                    $multi20 = floor($counter / 20) * 20;
                    $resto20 = ($counter % 20);
                    $line .= $this->compressionTable[$multi20];
                    if ($resto20 != 0) {
                        $line .= $this->compressionTable[$resto20] . $aux;
                    } else {
                        $line .= $aux;
                    }
                } else {
                    $line .= $this->compressionTable[$counter] . $aux;
                }
                $counter = 1;
                $firstChar = true;
                if ($line === $previousLine) {
                    $compressedData .= ':';
                } else {
                    $compressedData .= $line;
                }
                $previousLine = $line;
                $line = '';
                continue;
            }
            if ($aux === $char) {
                $counter++;
            } else {
                if ($counter > 20) {
                    $multi20 = floor($counter / 20) * 20;
                    $resto20 = ($counter % 20);
                    $line .= $this->compressionTable[$multi20];
                    if ($resto20 != 0) {
                        $line .= $this->compressionTable[$resto20] . $aux;
                    } else {
                        $line .= $aux;
                    }
                } else {
                    $line .= $this->compressionTable[$counter] . $aux;
                }
                $counter = 1;
                $aux = $char;
            }
        }
        return $compressedData;
    }

    /**
     * If a pixel value is greater than the threshold it will be converted to black during the image encoding
     *
     * @param int $threshold 0 to 765
     */
    public function setBlackThreshold(int $threshold) : void
    {
        $this->blackThreshold = $threshold;
    }
}
