# `^FH` — Field Hexadecimal Indicator

## Overview

The `^FH` (Field Hexadecimal Indicator) command tells the printer to interpret underscore-prefixed hex sequences within the following `^FD` data as literal characters. This is essential for embedding characters that would otherwise be interpreted as ZPL control characters (e.g. `^`, `~`, `_`).

The library automatically prepends `^FH` to every text field rendered via `drawText()` or `drawCell()`.

---

## ZPL Syntax

```
^FHa
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `a` | Indicator character used as the hex prefix (default `_`) | `_` |

When `^FH` is active, any sequence like `_XX` in the field data is interpreted as the character with hex code `XX`.

---

## PHP API

`^FH` is inserted automatically before `^FD` in all text-drawing methods. You do not need to add it manually.

To emit it directly:

```php
$builder->addCommand('FH');
// or:
$builder->FH();
```

---

## Character Escaping

The library maps the following characters to their hex sequences automatically:

| Original | Hex Sequence | Reason |
|----------|-------------|--------|
| `_` | `_5F` | Escape character itself |
| `^` | `_5E` | ZPL command prefix |
| `~` | `_7E` | ZPL firmware command prefix |
| `{` | `_7B` | ZPL control character |
| `}` | `_7D` | ZPL control character |
| `[` | `_5B` | ZPL control character |
| `]` | `_5D` | ZPL control character |
| `#` | `_23` | ZPL format delimiter |
| `%` | `_25` | ZPL format delimiter |

---

## Examples

### Text with special characters (automatic)

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawText(10, 10, 'Price: 100% off');
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO10,10
^FH^FDPrice: 100_25 off^FS
^FWN
^XZ
```

The `%` is automatically converted to `_25` because `^FH` is active.

### Manual field with hex indicator

```php
$builder->addCommand('FH');
$builder->addCommand('FD', 'Cost_3A 100_25');
$builder->addCommand('FS');
// Output: ^FH^FDCost_3A 100_25^FS
// Prints: "Cost: 100%"
```

---

## Notes

- `^FH` only applies to the immediately following `^FD` field.
- The default hex indicator character is `_` (underscore). If your data contains underscores, they are automatically escaped to `_5F` by the library.
- You do not need to add `^FH` manually when using `drawText()` or `drawCell()`.
