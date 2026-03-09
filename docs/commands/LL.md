# `^LL` — Label Length

## Overview

The `^LL` (Label Length) command defines the height of the label in dots. This tells the printer where the bottom of the label is.

---

## ZPL Syntax

```
^LLa
```

| Parameter | Description |
|-----------|-------------|
| `a` | Label height in dots |

---

## PHP API

```php
$builder->setHeight(float $height): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$height` | `float` | Label height in the current unit (dots or mm). Converted to dots automatically. |

---

## Examples

### Set label height to 100 mm (203 DPI)

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->setHeight(100);
echo $builder->toZpl();
```

Output:

```
^XA
^LL799
^XZ
```

### Set label height directly in dots

```php
$builder = new ZplBuilder(); // Unit::DOTS (default)
$builder->setHeight(600);
echo $builder->toZpl();
```

Output:

```
^XA
^LL600
^XZ
```

---

## Notes

- If `$height` converts to `0` dots, the command is not emitted.
- Combine with `setWidth()` (`^PW`) to fully define the label dimensions.
- The default printer resolution is 203 DPI; use `setDpi()` to change it.
