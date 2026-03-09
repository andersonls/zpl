# `^GB` — Graphic Box

## Overview

The `^GB` (Graphic Box) command draws a rectangle or line on the label. It can render open frames, solid filled boxes, or straight lines depending on the parameters.

---

## ZPL Syntax

```
^GBw,h,t,c,r
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `w` | Width in dots | — |
| `h` | Height in dots | — |
| `t` | Border thickness in dots | `1` |
| `c` | Color: `B` black, `W` white | `B` |
| `r` | Corner rounding (0–8) | `0` |

---

## PHP API

### Draw rectangle

```php
$builder->drawRect(
    float $x,
    float $y,
    float $width,
    float $height,
    float $thickness = 0,
    string $color = 'B',
    float $round = 0,
    bool $invert = false
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | `float` | X position of the top-left corner. |
| `$y` | `float` | Y position of the top-left corner. |
| `$width` | `float` | Box width in the current unit. |
| `$height` | `float` | Box height in the current unit. |
| `$thickness` | `float` | Border thickness. `0` uses the default (3 dots). |
| `$color` | `string` | `'B'` (black) or `'W'` (white). |
| `$round` | `float` | Corner rounding level 0–8. |
| `$invert` | `bool` | Invert colors (adds `^FR`). |

### Draw line

```php
$builder->drawLine(
    float $x1,
    float $y1,
    float $x2,
    float $y2,
    float $thickness = 0,
    string $color = 'B',
    bool $invert = false
): void
```

`drawLine()` delegates to `drawRect()` with the difference between the two points as the width and height.

---

## Examples

### Simple border rectangle

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawRect(5, 5, 50, 30);
echo $builder->toZpl();
```

Output:

```
^XA
^FO5,5^GB50,30,3,B,0^FS
^XZ
```

### Filled black rectangle (thick border)

```php
$builder->drawRect(10, 10, 100, 50, 50); // thickness = 50 ≈ solid fill
// Output: ^FO10,10^GB100,50,50,B,0^FS
```

### Rounded corners

```php
$builder->drawRect(5, 5, 80, 40, 3, 'B', 4);
// Output: ^FO5,5^GB80,40,3,B,4^FS
```

### White rectangle (erase)

```php
$builder->drawRect(10, 10, 60, 30, 0, 'W');
// Output: ^FO10,10^GB60,30,3,W,0^FS
```

### Inverted (color-reversed) rectangle

```php
$builder->drawRect(5, 5, 100, 30, 0, 'B', 0, true);
// Output: ^FO5,5^FR^GB100,30,3,B,0^FS
```

### Horizontal line

```php
$builder->drawLine(0, 50, 200, 50, 2);
// Draws a 2-dot thick horizontal line from x=0 to x=200 at y=50
```

---

## Notes

- When `$thickness` is `0`, the library uses `3` dots as the default (not `1`).
- A `$thickness` equal to the smaller of `$width` and `$height` produces a solid filled box.
- Color `'W'` (white) is useful for "erasing" parts of a black background.
- Corner rounding (`$round`) accepts values `0`–`8`, where `8` is the most rounded.
