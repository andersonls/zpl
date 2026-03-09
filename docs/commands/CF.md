# `^CF` — Set Font

## Overview

The `^CF` (Change Font) command selects the default font and its size for all subsequent text fields on the label.

---

## ZPL Syntax

```
^CFf,s,w
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `f` | Font identifier (e.g. `0`, `A`–`Z`) | `0` |
| `s` | Character height in dots | — |
| `w` | Character width in dots (optional) | Same as height |

---

## PHP API

```php
$builder->setFont(string $font, float $size, ?float $width = null): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$font` | `string` | Font identifier (`'0'`, `'A'`–`'Z'`). Mapped through the configured `FontMapper`. |
| `$size` | `float` | Font size in the current unit (dots or mm). Converted to dots automatically. |
| `$width` | `float\|null` | Optional character width override in the current unit. |

> **Size conversion:** the library converts the size using `round($size * ($dpi * 0.014))`. At 203 DPI a size of `16` mm produces `~45` dots.

---

## Examples

### Set font 0 at size 16 (mm units)

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->setFont('0', 16);
echo $builder->toZpl();
```

Output:

```
^XA
^CF0,45
^XZ
```

### Set font with explicit width

```php
$builder->setFont('0', 16, 12);
// Output: ^CF0,45,34
```

### Custom font identifier

```php
$builder->setFont('A', 10);
// Output: ^CFA,29
```

### Dynamic call (arbitrary command)

```php
$builder->CF('A', 30);
// Output: ^CFA,30
```

---

## Font Mapping

The `setFont()` method passes the font identifier through the active `FontMapper`. The default mapper (`\Zpl\Fonts\Generic`) does not translate identifiers, so `'0'` stays `'0'`.

A printer-specific mapper (e.g. `\Zpl\Fonts\Bematech\Lb1000`) can remap logical names to printer-internal font codes:

```php
$builder->setFontMapper(new \Zpl\Fonts\Bematech\Lb1000());
$builder->setFont('0', 16); // mapped to the Bematech-specific font
```

---

## Notes

- `^CF` sets the **default** font; individual fields may override it with `^A`.
- Call `setFont()` before drawing text to ensure the correct size is applied.
- At 203 DPI, the formula `size × 0.014 × 203` converts mm to dots.
