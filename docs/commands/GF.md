# `^GF` — Graphic Field (Image)

## Overview

The `^GF` (Graphic Field) command embeds a raster image directly in the ZPL stream as hex-encoded bitmap data. The library handles all encoding, including optional Floyd-Steinberg dithering for better greyscale reproduction.

Supported input formats: **PNG** and **JPG**.

---

## ZPL Syntax

```
^GFa,b,c,d,data
```

| Parameter | Description |
|-----------|-------------|
| `a` | Format: `A` (ASCII hex) |
| `b` | Total byte count |
| `c` | Bytes per row |
| `d` | Row width in dots |
| `data` | Hex-encoded bitmap rows |

---

## PHP API

```php
$builder->drawGraphic(
    float $x,
    float $y,
    string $image,
    int $width = 0,
    bool $dithering = false
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | `float` | X position in the current unit. |
| `$y` | `float` | Y position in the current unit. |
| `$image` | `string` | Absolute path to the image file (PNG or JPG). |
| `$width` | `int` | Target width in dots. `0` uses the image's natural width. |
| `$dithering` | `bool` | Enable Floyd-Steinberg dithering for smoother greyscale output. |

---

## Examples

### Draw an image at its natural size

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->drawGraphic(10, 10, __DIR__ . '/logo.png');
echo $builder->toZpl();
```

Output (abbreviated):

```
^XA
^FO8,8
^GFA,1200,1200,20,<hex data>
^FS
^XZ
```

### Scale image to 200 dots wide

```php
$builder->drawGraphic(10, 10, __DIR__ . '/logo.png', 200);
```

### Enable dithering for greyscale images

```php
$builder->drawGraphic(10, 10, __DIR__ . '/photo.jpg', 300, true);
```

### Via arbitrary command (file path)

```php
$builder->addCommand('GF', '/path/to/image.png');
// The library detects 'GF' with a single path arg and invokes GraphicField automatically
```

---

## Implementation Details

The `\Zpl\Commands\GraphicField` class:

1. Loads the image using GD.
2. Optionally scales it to the target width (maintaining aspect ratio).
3. Applies optional Floyd-Steinberg dithering.
4. Converts each pixel to a 1-bit (black/white) value.
5. Packs bits into bytes and encodes them as uppercase hex strings.
6. Emits `^GFA,<totalBytes>,<totalBytes>,<rowBytes>,<hexRows>`.

---

## Notes

- The GD extension must be enabled in PHP (`ext-gd`).
- Large images can produce very long ZPL strings; scale images to the minimum required width before encoding.
- Dithering improves the visual quality of greyscale/colour images when converted to 1-bit monochrome.
- The library currently supports only the ASCII hex (`A`) format of `^GF`.
