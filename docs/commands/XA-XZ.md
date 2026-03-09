# `^XA` / `^XZ` — Label Start and End

## Overview

Every ZPL label must be wrapped between a **Label Start** (`^XA`) command and a **Label End** (`^XZ`) command. All other ZPL commands are placed between these two delimiters.

The library automatically adds `^XA` and `^XZ` when you call `toZpl()`.

---

## ZPL Syntax

```
^XA
  ... label commands ...
^XZ
```

---

## PHP API

These commands are managed internally by the library. You do not call them directly.

```php
// toZpl() automatically wraps all queued commands
$zpl = $builder->toZpl();
```

To start a new label inside the same ZPL payload (multi-page printing), use `newPage()`:

```php
$builder->newPage();
```

This inserts a closing `^XZ` followed by a new opening `^XA`.

---

## Parameters

These commands take no parameters.

---

## Examples

### Single label

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->drawText(5, 5, 'Hello');
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO5,5
^FH^FDHello^FS
^FWN
^XZ
```

### Multi-page label

```php
$builder = new ZplBuilder(Unit::MM);
$builder->drawText(5, 5, 'Label 1');

$builder->newPage();

$builder->drawText(5, 5, 'Label 2');
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO5,5
^FH^FDLabel 1^FS
^FWN
^XZ
^XA
^FWN
^FO5,5
^FH^FDLabel 2^FS
^FWN
^XZ
```

### Injecting raw commands before / after the label

```php
$builder->addPreCommand('^LH0,0');   // inserted before ^XA
$builder->addPostCommand('^PQ1');    // inserted after ^XZ
```

---

## Notes

- All ZPL content must reside between `^XA` and `^XZ`.
- Each `newPage()` call closes the current label and opens a fresh one.
- Pre-commands and post-commands (added via `addPreCommand()` / `addPostCommand()`) appear outside the `^XA`/`^XZ` block.
