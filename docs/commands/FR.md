# `^FR` — Field Reverse

## Overview

The `^FR` (Field Reverse) command inverts the colors of the next field. Black pixels become white and white pixels become black. This creates a "white-on-black" or "white-on-dark-background" effect.

`^FR` is applied by passing `$invert = true` to any drawing method that supports it.

---

## ZPL Syntax

```
^FR
```

`^FR` takes no parameters. It is placed immediately before the field data command (`^FD`, `^GB`, `^GC`, etc.).

---

## PHP API

The `$invert` parameter is supported by the following methods:

```php
$builder->drawText(float $x, float $y, string $text, Orientation $orientation, bool $invert = false): void
$builder->drawRect(float $x, float $y, float $width, float $height, float $thickness, string $color, float $round, bool $invert = false): void
$builder->drawCircle(float $x, float $y, float $diameter, float $thickness, string $color, bool $invert = false): void
$builder->drawSerialNumber(float $x, float $y, string $start, int $step, bool $pad, bool $invert = false): void
```

Setting `$invert = true` inserts `^FR` before the field.

---

## Examples

### Inverted text

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;
use Zpl\Enums\Orientation;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawText(10, 10, 'Inverted', Orientation::NORMAL, true);
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO10,10
^FR
^FH^FDInverted^FS
^FWN
^XZ
```

### Inverted rectangle

```php
$builder->drawRect(10, 10, 100, 50, 0, 'B', 0, true);
// Output: ^FO10,10^FR^GB100,50,3,B,0^FS
```

---

## Notes

- `^FR` only affects the single field immediately following it.
- The effect depends on the background: on a white label, inverted text appears white with a black fill.
- To create a black background block with white text, combine an inverted filled rectangle (`^GB` with `^FR`) and inverted text.
