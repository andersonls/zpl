# `^TB` — Text Block

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^TB` (Text Block) command renders a block of text with **automatic word wrapping** and configurable maximum width and height. It is an alternative to `^FB` (Field Block) and provides more explicit control over the block dimensions.

Unlike `^FB` which works in conjunction with `^FO` and `^FD`, `^TB` is a self-contained command that combines position, dimensions, and text in one block.

---

## ZPL Syntax

```
^TBa,w,h
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `a` | Rotation: `N` normal, `R` rotated 90°, `I` inverted, `B` bottom-up | `N` |
| `w` | Block width in dots | — |
| `h` | Block height in dots | — |

The text content follows via `^FD…^FS` as usual.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Text block at current position
$builder->addCommand('FO', 10, 10);
$builder->TB('N', 200, 100);
$builder->addCommand('FH');
$builder->addCommand('FD', 'This is a long text that will wrap automatically within the block.');
$builder->addCommand('FS');
```

---

## `^TB` vs `^FB`

| Feature | `^TB` | `^FB` |
|---------|-------|-------|
| Word wrapping | Yes | Yes |
| Rotation | Yes (`N`, `R`, `I`, `B`) | No |
| Height limit | Yes | Yes (as max lines) |
| Alignment | Inherits from `^A`/`^CF` | Explicit parameter |
| Dedicated PHP method | Not yet | Used by `drawCell()` |

---

## Examples

### Basic wrapping text block

```
^XA
^CF0,30
^FO10,10
^TBN,300,150
^FH^FDThis is a long product description that wraps automatically.^FS
^XZ
```

### Rotated text block

```
^XA
^CF0,25
^FO10,10
^TBR,150,300
^FH^FDRotated wrapping text^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->setFont('0', 30);
$builder->addCommand('FO', 10, 10);
$builder->addCommand('TB', 'N', 300, 150);
$builder->addCommand('FH');
$builder->addCommand('FD', 'Long description that wraps.');
$builder->addCommand('FS');
echo $builder->toZpl();
```

---

## Notes

- `^TB` requires firmware version V60.14 or later on Zebra printers. Older firmware may not support it.
- If the text exceeds the block height, it is truncated at the bottom of the block.
- For text blocks without rotation, `^FB` (used by the library's `drawCell()`) is equivalent and is broadly compatible with older firmware.
- The block width and height are always specified in dots regardless of the `Unit` setting in the PHP library.
