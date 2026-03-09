# `^LH` — Label Home

## Overview

The `^LH` (Label Home) command sets the origin point (home position) of the label. All field coordinates (`^FO`) are relative to this origin. The default origin is `0,0` (top-left corner of the printable area).

---

## ZPL Syntax

```
^LHx,y
```

| Parameter | Description |
|-----------|-------------|
| `x` | Horizontal origin in dots |
| `y` | Vertical origin in dots |

---

## PHP API

```php
$builder->setHome(float $x, float $y): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | `float` | Horizontal origin in the current unit (dots or mm). |
| `$y` | `float` | Vertical origin in the current unit (dots or mm). |

---

## Examples

### Reset home to top-left

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->setHome(0, 0);
echo $builder->toZpl();
```

Output:

```
^XA
^LH0,0
^XZ
```

### Shift the origin by 5 mm on each axis

```php
$builder->setHome(5, 5);
// At 203 DPI: 5 mm × (203 / 25.4) ≈ 40 dots → ^LH40,40
```

### Via raw pre-command

```php
$builder->addPreCommand('^LH0,0');
```

---

## Notes

- `^LH` is typically set once at the beginning of the label.
- Using `addPreCommand()` is a common alternative when the home position must be outside the `^XA`/`^XZ` block.
- Changing the home position shifts every subsequent field on the label.
