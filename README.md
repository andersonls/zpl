## This library is a php wrapper for the ZPL Programming Language.
---
## Installation:

```bash
composer require andersonls/zpl
```

## How to use

Below are concise examples showing common (simple) and more advanced usages of the library.

Simple usage (create a label with one cell and print it):

```php
<?php
require __DIR__ . '/vendor/autoload.php';

// Use millimeters for coordinates and dimensions
use Zpl\Enums\Unit;
$driver = new \Zpl\ZplBuilder(Unit::MM);

// Set encoding (optional)
$driver->setEncoding(28);

$driver->setFont('0', 16);
$driver->setXY(0, 0);
$driver->drawCell(100, 10, 'Hello World', true, true, 'C');

$zpl = $driver->toZpl();

\Zpl\Printer::printer('192.168.1.1')->send($zpl);
```

Advanced usage (multiple elements, barcodes, QR codes, graphics, pages and raw commands):

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Zpl\Enums\Unit;
$driver = new \Zpl\ZplBuilder(Unit::MM);
$driver->setFontMapper(new \Zpl\Fonts\Generic());
$driver->setDpi(300); // change printer DPI when needed

// Draw shapes
$driver->drawRect(5, 5, 50, 30);
$driver->drawCircle(60, 5, 25);

// Text with explicit coordinates
use Zpl\Enums\Orientation;
use Zpl\Enums\Barcode;
$driver->drawText(5, 40, 'Product: ABC-123', Orientation::NORMAL);

// Code 128 barcode (x, y, height, data, print human-readable?)
$driver->drawCode128(5, 50, 20, 'ABC123456789', true);

// QR Code (x, y, data, module size)
$driver->drawQrCode(50, 50, 'https://example.com/product/ABC-123', 6);

// Add an image/graphic. width is optional and will scale the graphic field in dots
$driver->drawGraphic(120, 10, __DIR__ . '/logo.png', 200);

// Add custom/pre/post raw commands (you can use ^XA, ^XZ and any other ZPL commands)
$driver->addPreCommand('^LH0,0'); // set label home
$driver->addPostCommand('^PQ1');  // print quantity 1

// New page (adds a new label) - useful if you want to print multiple labels in one ZPL payload
$driver->newPage();
$driver->drawText(5, 5, 'Second label');

// You can call arbitrary ZPL commands using method-like calls (dynamic): CF is same as ^CF
$driver->CF('A', 30); // ^CFA,30  (be careful with arguments and expected format)

\Zpl\Printer::printer('192.168.1.100')->send($driver->toZpl());
```

## Macros

You can extend the ZplBuilder with custom macros to add reusable commands or behaviors. Macros are registered using the `macro()` method and can be called as if they were native methods.

### Registering a Macro

```php
$builder = new \Zpl\ZplBuilder('mm');
\Zpl\ZplBuilder::macro('drawCustomBox', function($x, $y, $w, $h) {
    $this->drawRect($x, $y, $w, $h);
    $this->drawText($x + 2, $y + 2, 'Custom');
});
```

### Using a Macro

```php
$builder->drawCustomBox(10, 10, 50, 20);
```

### Notes
- Macros have access to the builder instance via `$this`.
- You can override existing methods, but use caution to avoid breaking core functionality.
- Macros are useful for encapsulating label patterns, custom shapes, or repetitive tasks.

Notes and tips

- Units: the constructor accepts a Unit enum (for example: `Unit::MM` or `Unit::DOTS`). When using `Unit::MM`, coordinates are converted to printer dots using the configured DPI (default 203).
- DPI: default resolution is 203 DPI. Use `setDpi()` to change it or `getDpi()` to read it.
- Font mapping: use `setFontMapper()` to provide a mapper implementing `\Zpl\Fonts\AbstractMapper` (the included `\Zpl\Fonts\Generic` maps logical font ids to ZPL fonts).
- Special characters: common control characters are converted to their ZPL-safe hex sequences automatically (see library mappings for details).
- Debugging: write the ZPL to a file and inspect it or send it to a printer emulator before printing on physical media.

## Donations

If this project helps you somehow, you can give me a cup of coffee :)

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/donate/?hosted_button_id=KCZB8TGG63Y7W)
