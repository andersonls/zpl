# `^BQ` — QR Code

## Overview

The `^BQ` (Bar Code QR) command prints a QR Code 2D barcode. QR Codes can encode URLs, text, or binary data and are readable by smartphones and handheld scanners.

---

## ZPL Syntax

```
^BQo,m,M,l,d
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `o` | Orientation | `N` normal (only `N` is supported) | `N` |
| `m` | Model | `1` original, `2` enhanced | `2` |
| `M` | Magnification factor | `1`–`10` | `1` |
| `l` | Error correction level | `H` high, `Q` quartile, `M` medium, `L` low | `Q` |
| `d` | Mask value | `0`–`7` | `7` |

The field data (`^FD`) for QR Code uses the format `QA,<data>` where `A` is the error correction level.

---

## PHP API

```php
$builder->drawQrCode(
    float $x,
    float $y,
    string $data,
    int $size = 10
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | `float` | X position in the current unit. |
| `$y` | `float` | Y position in the current unit. |
| `$data` | `string` | Data to encode (URL, text, etc.). |
| `$size` | `int` | Module magnification factor (effectively the height parameter passed to the underlying `drawBarcode()` call). |

---

## Examples

### Basic QR Code

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->drawQrCode(50, 50, 'https://example.com/product/ABC-123', 6);
echo $builder->toZpl();
```

Output (approximate):

```
^XA
^FO399,399
^BQN,2,6
^FDQA,https://example.com/product/ABC-123^FS
^XZ
```

### Smaller QR Code

```php
$builder->drawQrCode(10, 10, 'Hello', 3);
// Uses size=3 as the magnification factor
```

### Via generic drawBarcode()

```php
use Zpl\Enums\Barcode;
$builder->drawBarcode(Barcode::QR, 10, 10, 6.0, 'https://example.com');
```

---

## Notes

- The library always uses model `2` (enhanced QR Code) and passes the `$size` parameter as the magnification factor.
- The field data is automatically prefixed with `QA,`. In the ZPL QR Code field data format the first character is the error correction level (`H`, `Q`, `M`, or `L`) and the second character is the character-set mode indicator (`A` = automatic). The library hardcodes `Q` (quartile, ~25 % data recovery) and `A` (automatic mode).
- QR Codes can hold up to ~4,000 alphanumeric characters, but shorter data yields smaller symbols that are easier to scan.
- Orientation is fixed to `N` (normal) in `drawQrCode()`; use `drawBarcode()` directly if you need rotation.
- See [All Barcode Types](barcodes.md) for the full list of supported symbologies.
