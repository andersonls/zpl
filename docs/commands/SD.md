# `~SD` — Set Darkness

## Overview

The `~SD` (Set Darkness) command adjusts the print darkness (burn intensity) of the printer. Increasing the darkness value produces bolder, darker prints; decreasing it produces lighter prints.

Note the `~` prefix — this is a **device-level** command and is sent directly to the printer firmware without being wrapped in `^XA`/`^XZ`.

---

## ZPL Syntax

```
~SDa
```

| Parameter | Description | Range |
|-----------|-------------|-------|
| `a` | Darkness value | 0.0 – 30.0 (increments of 0.1) |

---

## PHP API

```php
$builder->setDarkness(float $value): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | `float` | Darkness level from `0.0` (lightest) to `30.0` (darkest), in 0.1 increments. |

---

## Examples

### Set medium darkness

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->setDarkness(15.5);
echo $builder->toZpl();
```

Output:

```
^XA
~SD15.5
^XZ
```

### Set maximum darkness

```php
$builder->setDarkness(30);
// Output: ~SD30
```

---

## Notes

- The `~SD` command persists on the printer until changed; it is not reset between labels.
- Recommended starting point: `15`–`20` for most media types.
- Values outside `0`–`30` may be ignored or clamped by the printer firmware.
