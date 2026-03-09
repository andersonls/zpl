# `^PW` — Print Width

## Overview

The `^PW` (Print Width) command sets the width of the printable area on the label in dots.

---

## ZPL Syntax

```
^PWa
```

| Parameter | Description |
|-----------|-------------|
| `a` | Label width in dots |

---

## PHP API

```php
$builder->setWidth(float $width): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$width` | `float` | Label width in the current unit (dots or mm). Converted to dots automatically. |

---

## Examples

### Set label width to 100 mm (203 DPI)

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->setWidth(100);
echo $builder->toZpl();
```

Output:

```
^XA
^PW799
^XZ
```

### Set width directly in dots

```php
$builder = new ZplBuilder(); // Unit::DOTS (default)
$builder->setWidth(400);
echo $builder->toZpl();
```

Output:

```
^XA
^PW400
^XZ
```

---

## Notes

- If `$width` converts to `0` dots, the command is not emitted.
- Combine with `setHeight()` (`^LL`) to fully define the label dimensions.
- The default printer resolution is 203 DPI; use `setDpi()` to change it.
