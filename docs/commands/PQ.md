# `^PQ` — Print Quantity

## Overview

The `^PQ` (Print Quantity) command tells the printer how many copies of the label to print. It also supports pause-and-cut behaviour and replication of labels within a cut group.

---

## ZPL Syntax

```
^PQq,p,r,o,e
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `q` | Total number of labels to print | `1` |
| `p` | Pause and cut value (labels per cut group) | `0` (no pause) |
| `r` | Replicates of each serial number | `0` |
| `o` | Override pause count | `N` |
| `e` | Cut on error label | `Y` |

---

## PHP API

```php
$builder->setQuantity(int $quantity, int $pauseQty = 0, int $replicate = 0): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$quantity` | `int` | Number of labels to print. |
| `$pauseQty` | `int` | After how many labels to pause (0 = no pause). |
| `$replicate` | `int` | Number of replicates per serial number increment. Values below `0` are treated as `0`. |

---

## Examples

### Print one label

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->drawText(5, 5, 'Hello');
$builder->setQuantity(1);
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO5,5
^FH^FDHello^FS
^FWN
^PQ1,0,0
^XZ
```

### Print 10 labels

```php
$builder->setQuantity(10);
// Output: ^PQ10,0,0
```

### Print 100 labels, pause every 10

```php
$builder->setQuantity(100, 10);
// Output: ^PQ100,10,0
```

### Via post-command (outside the label block)

```php
$builder->addPostCommand('^PQ1');
```

---

## Notes

- `setQuantity()` inserts `^PQ` inside the `^XA`/`^XZ` block, before `^XZ`.
- For global quantity control that applies across multiple labels, use `addPostCommand('^PQ1')` instead.
- The replication value is clamped to `0` if a negative value is provided.
