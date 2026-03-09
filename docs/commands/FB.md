# `^FB` — Field Block

## Overview

The `^FB` (Field Block) command constrains a text field to a rectangular block. It enables automatic word wrapping, multi-line text, and horizontal alignment within the block bounds.

The library uses `^FB` internally when you call `drawCell()` with an alignment value.

---

## ZPL Syntax

```
^FBw,l,s,j,h
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `w` | Block width in dots | — |
| `l` | Maximum number of lines | `1` |
| `s` | Line spacing adjustment in dots | `0` |
| `j` | Justification: `L` left, `C` center, `R` right, `J` justified | `L` |
| `h` | Hanging indent in dots | `0` |

---

## PHP API

`^FB` is emitted automatically by `drawCell()` when an alignment is provided:

```php
$builder->drawCell(
    float $width,
    float $height,
    string $text,
    bool $border = false,
    bool $ln = false,
    ?Align $align = null
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | `float` | Cell width in the current unit. |
| `$height` | `float` | Cell height in the current unit. |
| `$text` | `string` | Text content. |
| `$border` | `bool` | Draw a border rectangle around the cell. |
| `$ln` | `bool` | Move cursor to the next line after the cell. |
| `$align` | `Align\|null` | Text alignment: `Align::LEFT`, `Align::CENTER`, `Align::RIGHT`, `Align::JUSTIFIED`. |

### Alignment Enum

```php
use Zpl\Enums\Align;

Align::LEFT       // 'L'
Align::CENTER     // 'C'
Align::RIGHT      // 'R'
Align::JUSTIFIED  // 'J'
```

---

## Examples

### Centered cell

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;
use Zpl\Enums\Align;

$builder = new ZplBuilder(Unit::MM);
$builder->setXY(0, 0);
$builder->drawCell(100, 10, 'Hello World', true, true, Align::CENTER);
echo $builder->toZpl();
```

Output (approximate, dots depend on DPI):

```
^XA
^FO0,0^GB799,80,3,B,0^FS
^FO10,20
^FB789,60,0,C
^FH^FDHello World^FS
^XZ
```

### Right-aligned cell without border

```php
$builder->setXY(0, 0);
$builder->drawCell(60, 10, 'Total:', false, false, Align::RIGHT);
```

### Raw command

```php
$builder->addCommand('FB', 200, 3, 0, 'C');
// Output: ^FB200,3,0,C
```

---

## Notes

- When `$align` is `null`, no `^FB` command is emitted and the text is positioned at the raw `^FO` coordinate.
- The block width passed to `^FB` is `toDots($width) - offsetX` to account for the internal padding offset.
- The block height is `toDots($height) - offsetY` where `offsetY = toDots($height) / 4`.
