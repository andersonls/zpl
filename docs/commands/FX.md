# `^FX` — Comment

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^FX` (Comment) command inserts a **non-printing comment** into a ZPL label format. The printer parses and discards the comment; it has no effect on the printed output.

Comments are useful for:
- Documenting complex label formats.
- Annotating fields for human readers and AI tools.
- Temporarily "commenting out" fields during development.

---

## ZPL Syntax

```
^FXcomment text
```

| Parameter | Description |
|-----------|-------------|
| `comment text` | Any text; the printer ignores it. The comment ends at the next `^` or `~` character. |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Dynamic method call
$builder->FX('This is a comment');
// Output: ^FXThis is a comment

// Via addCommand()
$builder->addCommand('FX', 'Header section');
// Output: ^FXHeader section
```

---

## Examples

### Annotating a label format

```
^XA
^FXLabel header
^CF0,40
^FO10,10^FH^FDProduct Name^FS

^FXBarcode section - Code 128
^FO10,60
^BCN,50,Y,N,N,A
^FDABC-123^FS

^FXFooter
^FO10,130^FH^FDLot: 001^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);

$builder->addCommand('FX', 'Label header');
$builder->setFont('0', 40);
$builder->drawText(10, 10, 'Product Name');

$builder->addCommand('FX', 'Barcode section');
$builder->drawCode128(10, 60, 50, 'ABC-123', true);

echo $builder->toZpl();
```

---

## Notes

- `^FX` comments are not preserved in the final ZPL output sent to the printer; they are parsed and discarded.
- A comment ends at the next `^` or `~` character. Do not use `^` or `~` inside a comment.
- Comments do not affect label rendering, field positions, or any printer state.
- Unlike `//` or `/* */` comments in programming languages, `^FX` is a ZPL command and must be placed between `^XA` and `^XZ`.
