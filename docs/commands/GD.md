# `^GD` — Graphic Diagonal Line

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^GD` (Graphic Diagonal Line) command draws a diagonal line on the label. The line is drawn from the current `^FO` position to a point offset by the specified width and height.

---

## ZPL Syntax

```
^GDw,h,t,c,o
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `w` | Width of the bounding box in dots | 3–32000 | — |
| `h` | Height of the bounding box in dots | 3–32000 | — |
| `t` | Line thickness in dots | 1–32000 | `1` |
| `c` | Line color: `B` black, `W` white | `B`, `W` | `B` |
| `o` | Orientation: `R` right-leaning (`/`), `L` left-leaning (`\`) | `R`, `L` | `R` |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Dynamic method call (position + diagonal line)
$builder->addCommand('FO', 10, 10);
$builder->GD(100, 50, 3, 'B', 'R');
$builder->addCommand('FS');
// Output: ^FO10,10^GD100,50,3,B,R^FS

// Via addCommand()
$builder->addCommand('FO', 10, 10);
$builder->addCommand('GD', 100, 50, 3, 'B', 'L');
$builder->addCommand('FS');
// Output: ^FO10,10^GD100,50,3,B,L^FS
```

---

## Examples

### Right-leaning diagonal line (`/`)

```
^XA
^FO10,10
^GD100,50,3,B,R
^FS
^XZ
```

Draws a diagonal line starting at (10, 10) going right and down to (110, 60).

### Left-leaning diagonal line (`\`)

```
^XA
^FO10,10
^GD100,50,3,B,L
^FS
^XZ
```

### White diagonal line (erase)

```
^XA
^FO10,10
^GD80,40,5,W,R
^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('FO', 10, 10);
$builder->addCommand('GD', 100, 50, 3, 'B', 'R');
$builder->addCommand('FS');
echo $builder->toZpl();
```

---

## Notes

- The line is drawn inside the bounding box defined by `w` × `h`.
- Orientation `R` draws from top-left to bottom-right; `L` draws from top-right to bottom-left.
- For **horizontal** and **vertical** lines, use `^GB` (see [`GB.md`](GB.md)) which is more efficient.
- Color `'W'` erases the line area (useful for overlaying graphics).
