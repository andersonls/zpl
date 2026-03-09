# `^FT` — Field Typeset

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^FT` (Field Typeset) command sets the position of the **baseline** of the next text field. Unlike `^FO` (which positions the top-left corner of the field), `^FT` positions the bottom of the text characters (the baseline), making it easier to align multiple text elements vertically.

`^FT` and `^FO` are interchangeable in most contexts; the key difference is the Y coordinate reference point.

---

## ZPL Syntax

```
^FTx,y,z
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `x` | Horizontal position in dots from the label home | `0` |
| `y` | Vertical position of the text **baseline** in dots | `0` |
| `z` | Justification: `0` left, `1` right, `2` auto | `0` |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Dynamic method call
$builder->FT(50, 100);
// Output: ^FT50,100

// Via addCommand()
$builder->addCommand('FT', 50, 100);
// Output: ^FT50,100
```

---

## `^FT` vs `^FO`

| Command | Y coordinate reference |
|---------|----------------------|
| `^FO` | Top-left corner of the field bounding box |
| `^FT` | Baseline of the text characters |

For large fonts or when mixing font sizes on the same line, `^FT` makes vertical alignment more predictable because the baseline stays constant regardless of font height.

---

## Examples

### Baseline-positioned text

```
^XA
^CF0,30
^FT10,50
^FH^FDHello^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->setFont('0', 30);
$builder->addCommand('FT', 10, 50);
$builder->addCommand('FH');
$builder->addCommand('FD', 'Hello');
$builder->addCommand('FS');
echo $builder->toZpl();
```

---

## Notes

- `^FT` is equivalent to `^FO` for most practical purposes; the difference only matters when precise baseline alignment is required.
- The library's `drawText()` uses `^FO` internally; use `addCommand('FT', x, y)` when you need baseline positioning.
- Justification parameter `z` behaves the same as in `^FO`.
