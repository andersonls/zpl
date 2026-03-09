# `^FD` / `^FS` — Field Data and Field Separator

## Overview

- **`^FD`** (Field Data) marks the beginning of the data to be printed in a field. It must follow a positioning command (`^FO`) and a field type command (e.g. `^A` for font, or a barcode command).
- **`^FS`** (Field Separator) marks the end of the field data and closes the field.

Together they form the most fundamental text/data printing pair in ZPL.

---

## ZPL Syntax

```
^FD<data>^FS
```

| Element | Description |
|---------|-------------|
| `<data>` | The text or barcode data to render |

---

## PHP API

`^FD` and `^FS` are inserted automatically by all drawing methods. You do not call them directly.

```php
$builder->drawText(float $x, float $y, string $text, Orientation $orientation, bool $invert): void
$builder->drawCell(float $width, float $height, string $text, ...): void
$builder->drawBarcode(...): void
$builder->drawCode128(...): void
$builder->drawCode39(...): void
$builder->drawQrCode(...): void
$builder->drawSerialNumber(...): void
```

All of the above methods emit `^FD<data>^FS` internally.

---

## Examples

### Basic text field

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawText(50, 100, 'Hello, World!');
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO50,100
^FH^FDHello, World!^FS
^FWN
^XZ
```

### Barcode data field

```php
$builder->drawCode128(5, 50, 20, 'ABC-123', true);
```

Internally produces:

```
^FO5,50
^BCN,20,Y,N,N,A
^FDABC-123^FS
```

### Raw command

```php
$builder->addCommand('FD', 'My raw data');
$builder->addCommand('FS');
```

---

## Special Character Handling

When rendering text with `drawText()` or `drawCell()`, the library automatically:

1. Prefixes the field with `^FH` (see [`^FH`](FH.md)) to enable hex escape sequences.
2. Converts control characters to their hex equivalents:

| Character | Escaped As |
|-----------|-----------|
| `_` | `_5F` |
| `^` | `_5E` |
| `~` | `_7E` |
| `{` | `_7B` |
| `}` | `_7D` |
| `[` | `_5B` |
| `]` | `_5D` |
| `#` | `_23` |
| `%` | `_25` |

This ensures that ZPL control characters embedded in text are treated as literal characters.

---

## Notes

- Every `^FD` must be closed with `^FS`.
- The field data must not contain raw `^` or `~` characters unless they are hex-escaped via `^FH`.
- The library handles escaping transparently in all high-level drawing methods.
