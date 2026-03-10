<?php

namespace Zpl\Commands;

use GdImage;

class Dithering
{
    /**
     * Apply Floyd 6Steinberg dithering to the given image in-place.
     */
    public static function apply(GdImage $im, int $threshold = 128): void
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
