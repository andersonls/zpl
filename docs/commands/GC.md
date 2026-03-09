# `^GC` — Graphic Circle

## Overview

The `^GC` (Graphic Circle) command draws a circle on the label. The circle is defined by its diameter and border thickness.

---

## ZPL Syntax

```
^GCd,t,c
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `d` | Diameter in dots | — |
| `t` | Border thickness in dots | `1` |
| `c` | Color: `B` black, `W` white | `B` |

---

## PHP API

```php
$builder->drawCircle(
    float $x,
    float $y,
    float $diameter,
    float $thickness = 0,
    string $color = 'B',
    bool $invert = false
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | `float` | X position of the top-left corner of the bounding box. |
| `$y` | `float` | Y position of the top-left corner of the bounding box. |
| `$diameter` | `float` | Circle diameter in the current unit. |
| `$thickness` | `float` | Border thickness. `0` uses the default (3 dots). |
| `$color` | `string` | `'B'` (black) or `'W'` (white). |
| `$invert` | `bool` | Invert colors (adds `^FR`). |

---

## Examples

### Simple circle

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawCircle(60, 5, 25);
echo $builder->toZpl();
```

Output:

```
^XA
^FO60,5^GC25,3,B^FS
^XZ
```

### Thick-bordered circle

```php
$builder->drawCircle(10, 10, 50, 5);
// Output: ^FO10,10^GC50,5,B^FS
```

### Solid filled circle (thickness ≥ radius)

```php
$builder->drawCircle(10, 10, 40, 40);
// A circle where thickness equals diameter ≈ solid disc
```

### White circle (erase)

```php
$builder->drawCircle(10, 10, 30, 0, 'W');
// Output: ^FO10,10^GC30,3,W^FS
```

### Inverted circle

```php
$builder->drawCircle(10, 10, 30, 0, 'B', true);
// Output: ^FO10,10^FR^GC30,3,B^FS
```

---

## Notes

- The position (`$x`, `$y`) refers to the top-left corner of the circle's bounding box, not the center.
- When `$thickness` is `0`, the library substitutes `3` dots as the default.
- Color `'W'` (white) can be used to erase a circular region from a previously drawn element.
