# `^GE` — Graphic Ellipse

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^GE` (Graphic Ellipse) command draws an ellipse (oval) on the label. It is similar to `^GC` (circle), but allows independent control of width and height to create non-circular ovals.

---

## ZPL Syntax

```
^GEw,h,t,c
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `w` | Width of the ellipse in dots | 3–32000 | — |
| `h` | Height of the ellipse in dots | 3–32000 | — |
| `t` | Border thickness in dots | 2–32000 | `1` |
| `c` | Color: `B` black, `W` white | `B`, `W` | `B` |

> **Note:** Setting `t` to the minimum dimension (min of `w`, `h`) divided by 2 produces a solid filled ellipse.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Dynamic method call
$builder->addCommand('FO', 10, 10);
$builder->GE(100, 60, 3, 'B');
$builder->addCommand('FS');
// Output: ^FO10,10^GE100,60,3,B^FS

// Via addCommand()
$builder->addCommand('FO', 10, 10);
$builder->addCommand('GE', 100, 60, 3, 'B');
$builder->addCommand('FS');
```

---

## Examples

### Simple ellipse (border only)

```
^XA
^FO10,10
^GE100,60,3,B
^FS
^XZ
```

### Solid filled ellipse

```
^XA
^FO10,10
^GE80,40,40,B   ; thickness = height/2 = solid fill
^FS
^XZ
```

### White ellipse (erase)

```
^XA
^FO10,10
^GE60,40,3,W
^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('FO', 10, 10);
$builder->addCommand('GE', 100, 60, 3, 'B');
$builder->addCommand('FS');
echo $builder->toZpl();
```

---

## Notes

- The position (`^FO` coordinates) refers to the top-left corner of the ellipse's bounding box.
- To draw a **circle**, use `^GC` (see [`GC.md`](GC.md)) or use `^GE` with equal `w` and `h`.
- Color `'W'` erases the ellipse area, useful for creating holes in filled shapes.
