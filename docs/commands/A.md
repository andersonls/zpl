# `^A` / `^A@` — Use Font

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^A` (Scalable/Bitmapped Font) command selects a specific font and size for a **single field**, overriding the default set by `^CF`. It is the primary way to apply per-field font changes in ZPL.

- `^A` selects a **built-in** printer font (A–Z, 0).
- `^A@` selects a **downloaded or stored** font (from flash or RAM).

---

## ZPL Syntax

### Built-in font

```
^Af,o,h,w
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `f` | Font identifier | `0`, `A`–`Z` | — |
| `o` | Orientation | `N` normal, `R` rotated 90°, `I` inverted, `B` bottom-up | `N` |
| `h` | Character height in dots | 10–32000 | — |
| `w` | Character width in dots | 10–32000 | Same as `h` |

### Downloaded / stored font

```
^A@o,h,w,d:f.x
```

| Parameter | Description |
|-----------|-------------|
| `o` | Orientation (`N`, `R`, `I`, `B`) |
| `h` | Character height in dots |
| `w` | Character width in dots |
| `d:f.x` | Font storage location, name, and extension (e.g. `E:MYFONT.FNT`) |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Dynamic method call
$builder->A('0', 'N', 45);
// Output: ^A0,N,45

// Via addCommand()
$builder->addCommand('A', '0', 'N', 45, 45);
// Output: ^A0,N,45,45
```

---

## Difference from `^CF`

| Command | Scope | Usage |
|---------|-------|-------|
| `^CF` | Sets the **default** font for all subsequent fields | [`CF.md`](CF.md) |
| `^A` | Overrides the font for the **next single field** only | This page |

`^A` must immediately precede the `^FO…^FD…^FS` sequence it applies to.

---

## Examples

### Large font on a single field

```
^XA
^CF0,30          ; default size 30
^FO10,10
^A0,N,60,60      ; override: size 60 for this field only
^FDTitle Text^FS
^FO10,80
^FDNormal size^FS  ; back to ^CF default
^XZ
```

### Downloaded font

```
^XA
^FO10,10
^A@N,50,50,E:MYFONT.FNT
^FDHello^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->setFont('0', 30);          // default

// Field with large font
$builder->addCommand('FO', 10, 10);
$builder->A('0', 'N', 60, 60);
$builder->addCommand('FD', 'Title');
$builder->addCommand('FS');

// Field with default font
$builder->addCommand('FO', 10, 80);
$builder->addCommand('FD', 'Normal');
$builder->addCommand('FS');

echo $builder->toZpl();
```

---

## Notes

- `^A` only applies to the immediately following field.
- Height and width can differ to produce condensed or expanded glyphs.
- For consistent label-wide font changes, prefer `^CF` (see [`CF.md`](CF.md)).
- Orientation values match those of `^FW`: `N`, `R`, `I`, `B`.
