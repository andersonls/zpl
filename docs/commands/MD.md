# `^MD` — Media Darkness

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^MD` (Media Darkness) command adjusts the print darkness **relative** to the printer's current darkness setting. It differs from `~SD` (Set Darkness), which sets the darkness to an **absolute** value.

`^MD` is a format command (placed inside `^XA`/`^XZ`), making it easy to adjust darkness per-label without changing the printer's persistent setting.

---

## ZPL Syntax

```
^MDd
```

| Parameter | Description | Range |
|-----------|-------------|-------|
| `d` | Relative darkness delta | –30.0 to +30.0 (increments of 0.1) |

A positive value darkens the print; a negative value lightens it.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Darken by 5 relative to current setting
$builder->MD(5);
// Output: ^MD5

// Via addCommand()
$builder->addCommand('MD', 5);
// Output: ^MD5
```

---

## `^MD` vs `~SD`

| Command | Type | Scope |
|---------|------|-------|
| `~SD` | Device command | Sets **absolute** darkness (0–30); persists until changed |
| `^MD` | Format command | Applies a **relative** delta for the current label only |

For persistent configuration, use `~SD` (see [`SD.md`](SD.md)).
For per-label adjustments, use `^MD`.

---

## Examples

### Darken for a single label

```
^XA
^MD5
^CF0,30
^FO10,10^FH^FDBold Text^FS
^XZ
```

### Lighten for a single label

```
^XA
^MD-5
^FO10,10^FH^FDLight Text^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('MD', 5);
$builder->drawText(10, 10, 'Bold Text');
echo $builder->toZpl();
```

---

## Notes

- The delta is applied on top of the printer's current base darkness (set by `~SD` or the printer's menu).
- Values beyond the effective 0–30 range are clamped by the printer firmware.
- Use `~SD` when you need a fixed, absolute darkness level across all labels.
