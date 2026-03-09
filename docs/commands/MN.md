# `^MN` — Media Tracking

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^MN` (Media Tracking) command configures the printer's **label detection method** — how the printer identifies the beginning and end of each label on the media. Correct tracking configuration is essential for accurate label positioning.

---

## ZPL Syntax

```
^MNa,b
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `a` | Media type / tracking method | See table below | `N` |
| `b` | Black mark offset in dots (only for black mark tracking) | Integer | `0` |

### Media Tracking Values

| Value | Method | Description |
|-------|--------|-------------|
| `N` | Continuous | No gaps or marks; media feeds continuously |
| `Y` | Web sensing | Gap/notch between labels detected by transmissive sensor |
| `W` | Web sensing (same as `Y`) | Alternative value for gap sensing |
| `M` | Mark sensing | Black mark on the back of the media detected by reflective sensor |
| `A` | Auto | Printer automatically selects the best sensing method |
| `D` | Mark sensing (delayed) | Black mark sensing with feed delay |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Set gap sensing
$builder->MN('Y');
// Output: ^MNY

// Set black mark sensing
$builder->addCommand('MN', 'M');
// Output: ^MNM
```

---

## Examples

### Gap sensing (die-cut labels)

```
^XA
^MNY
^CF0,30
^FO10,10^FH^FDProduct^FS
^XZ
```

### Continuous media (no gaps)

```
^XA
^MNN
^LL400
^FO10,10^FH^FDContinuous label^FS
^XZ
```

### Black mark sensing with offset

```
^XA
^MNM,10
^FO10,10^FH^FDBlack mark label^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('MN', 'Y');
$builder->drawText(10, 10, 'Product');
echo $builder->toZpl();
```

---

## Notes

- `^MN` changes are persistent on the printer until explicitly reset.
- Use `A` (auto) on modern printers if you are uncertain of the media type.
- For continuous media with a fixed label height, combine `^MNN` with `^LL` (see [`LL.md`](LL.md)).
- Incorrect tracking configuration causes misprinted or skewed labels.
