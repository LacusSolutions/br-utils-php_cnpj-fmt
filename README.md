![cnpj-fmt for PHP](https://br-utils.vercel.app/img/cover_cnpj-fmt.jpg)

[![Packagist Version](https://img.shields.io/packagist/v/lacus/cnpj-fmt)](https://packagist.org/packages/lacus/cnpj-fmt)
[![Packagist Downloads](https://img.shields.io/packagist/dm/lacus/cnpj-fmt)](https://packagist.org/packages/lacus/cnpj-fmt)
[![PHP Version](https://img.shields.io/packagist/php-v/lacus/cnpj-fmt)](https://www.php.net/)
[![Test Status](https://img.shields.io/github/actions/workflow/status/LacusSolutions/br-utils-php/ci.yml?label=ci/cd)](https://github.com/LacusSolutions/br-utils-php/actions)
[![Last Update Date](https://img.shields.io/github/last-commit/LacusSolutions/br-utils-php)](https://github.com/LacusSolutions/br-utils-php)
[![Project License](https://img.shields.io/github/license/LacusSolutions/br-utils-php)](https://github.com/LacusSolutions/br-utils-php/blob/main/LICENSE)

> 🚀 **Full support for the [new alphanumeric CNPJ format](https://github.com/user-attachments/files/23937961/calculodvcnpjalfanaumerico.pdf).**

> 🌎 [Acessar documentação em português](https://github.com/LacusSolutions/br-utils-php/blob/main/packages/cnpj-fmt/README.pt.md)

A PHP utility to format CNPJ (Brazilian Business Tax ID).

## PHP Support

| ![PHP 8.2](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white) | ![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white) | ![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white) | ![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white) |
| --- | --- | --- | --- |
| Passing ✔ | Passing ✔ | Passing ✔ | Passing ✔ |

## Features

- ✅ **Alphanumeric CNPJ**: Full support for the new alphanumeric CNPJ format (introduced in 2026)
- ✅ **Flexible input**: Accepts `string` or `list<string>`; array elements are concatenated in order
- ✅ **Format agnostic**: Strips non-alphanumeric characters from string input and uppercases letters
- ✅ **Custom delimiters**: `dotKey`, `slashKey`, and `dashKey` may be empty, single- or multi-character strings
- ✅ **Masking**: Optional hiding of a character range with a configurable replacement string (`hidden`, `hiddenKey`, `hiddenStart`, `hiddenEnd`)
- ✅ **HTML & URL output**: Optional `escape` (HTML entities) and `encode` (URI component encoding, similar to JavaScript `encodeURIComponent`)
- ✅ **Length errors without throwing**: Invalid length after sanitization is handled via `onFail` (default returns an empty string)
- ✅ **Minimal dependencies**: Only [`lacus/utils`](https://packagist.org/packages/lacus/utils)
- ✅ **Error handling**: Type errors for wrong API use; option validation via dedicated exceptions

## Installation

```bash
# using Composer
$ composer require lacus/cnpj-fmt
```

## Import

```php
<?php

use Lacus\BrUtils\Cnpj\CnpjFormatter;
use Lacus\BrUtils\Cnpj\CnpjFormatterOptions;

use function Lacus\BrUtils\Cnpj\cnpj_fmt;
```

## Quick start

```php
<?php

use Lacus\BrUtils\Cnpj\CnpjFormatter;

$formatter = new CnpjFormatter();

$formatter->format('03603568000195');   // '03.603.568/0001-95'
$formatter->format('12ABC34500DE99');   // '12.ABC.345/00DE-99'
```

## Usage

The main entry points are the class `CnpjFormatter`, the options value object `CnpjFormatterOptions`, and the helper `cnpj_fmt()`.

### `CnpjFormatter`

- **`__construct`**: Optional default formatting options. The first parameter may be `null` or a `CnpjFormatterOptions` instance (that exact instance is stored; mutating it later affects subsequent `format` calls that do not pass per-call options). You may also pass option fields as named parameters (`hidden`, `hiddenKey`, `dotKey`, …). If the first argument is not a `CnpjFormatterOptions` instance, a new `CnpjFormatterOptions` is built from those named values. Example: `new CnpjFormatter(hidden: true, slashKey: '|')`.
- **`getOptions()`**: Returns the instance’s `CnpjFormatterOptions` (same object as used internally).
- **`format`**: `format(string|list<string> $cnpjInput, ?CnpjFormatterOptions|array $options, …named options…): string`

  Input is normalized by removing non-alphanumeric characters and uppercasing. If the sanitized length is not exactly **14**, the **`onFail`** callback is invoked with the original input and a `CnpjFormatterInputLengthException`; its return value is the result (nothing is thrown for length).

  If the input is not a `string` or a `list` of strings, **`CnpjFormatterInputTypeError`** is thrown.

  Per-call options are merged over the instance defaults for that call only (instance defaults are unchanged). You can pass a `CnpjFormatterOptions` instance or an associative array as the second argument, in addition to named parameters; later overrides win.

### `CnpjFormatterOptions`

Holds all formatter settings. Construct with named parameters, optional `overrides` (list of arrays and/or other `CnpjFormatterOptions` instances, merged in order). Exposes properties via magic `__get` / `__set` (`hidden`, `hiddenKey`, `hiddenStart`, `hiddenEnd`, `dotKey`, `slashKey`, `dashKey`, `escape`, `encode`, `onFail`).

- **`getAll()`**: Returns a shallow array snapshot of all options.
- **`set(...)`**: Updates multiple fields at once; returns `$this`.
- **`setHiddenRange(?int $hiddenStart, ?int $hiddenEnd)`**: Validates indices in **`[0, 13]`** (inclusive); if `hiddenStart > hiddenEnd`, values are swapped. `null` arguments fall back to defaults (`DEFAULT_HIDDEN_START` / `DEFAULT_HIDDEN_END`).
- **`getDefaultOnFail()`**: Returns the package default `onFail` closure (returns `''` for invalid length).

**`hiddenStart` / `hiddenEnd`**: Indices refer to the **14-character normalized CNPJ string** (before inserting punctuation). The inclusive range is replaced internally by placeholders, then `hiddenKey` is substituted (supports multi-character keys and empty string).

**Key options** (`hiddenKey`, `dotKey`, `slashKey`, `dashKey`): Must be strings and must not contain any character in `CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS` (reserved for internal formatting).

### Functional helper

`cnpj_fmt()` builds a new `CnpjFormatter` from the same constructor parameters (starting at the optional options / named args) and calls `format($cnpjInput)` once. Use named arguments for options: e.g. `cnpj_fmt($cnpj, hidden: true)` or `cnpj_fmt($cnpj, slashKey: '|')`.

```php
$cnpj = '03603568000195';

cnpj_fmt($cnpj);                 // '03.603.568/0001-95'
cnpj_fmt($cnpj, hidden: true);   // masked with defaults
cnpj_fmt(                        // '03603568|0001_95'
  $cnpj,
  dotKey: '',
  slashKey: '|',
  dashKey: '_',
);
```

### Object-oriented examples

```php
$formatter = new CnpjFormatter();
$cnpj = '03603568000195';

$formatter->format($cnpj);   // '03.603.568/0001-95'
$formatter->format(          // '03.603.###/####-##'
    $cnpj,
    hidden: true,
    hiddenKey: '#',
    hiddenStart: 5,
    hiddenEnd: 13
);
```

Default options on the instance; per-call overrides:

```php
$formatter = new CnpjFormatter(hidden: true);

$formatter->format($cnpj);                  // uses instance masking
$formatter->format($cnpj, hidden: false);   // this call only: unmasked
$formatter->format($cnpj);                  // back to instance defaults
```

### Input formats

**String:** Raw digits and/or letters, or already formatted CNPJ (e.g. `12.345.678/0009-10`, `12.ABC.345/00DE-99`). Non-alphanumeric characters are removed; lowercase letters are uppercased.

**Array of strings:** Each element must be a string; values are concatenated (e.g. per digit, grouped segments, or mixed with punctuation — all are stripped during normalization). Non-string elements are not allowed.

### Formatting options

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `hidden` | `?bool` | `false` | When `true`, replaces the inclusive index range `[hiddenStart, hiddenEnd]` on the normalized 14-character string before punctuation is applied |
| `hiddenKey` | `?string` | `'*'` | Replacement for each hidden position (may be multi-character or empty); must not use disallowed key characters |
| `hiddenStart` | `?int` | `5` | Start index `0`–`13` (inclusive) |
| `hiddenEnd` | `?int` | `13` | End index `0`–`13` (inclusive); if `hiddenStart > hiddenEnd`, they are swapped |
| `dotKey` | `?string` | `'.'` | Separator between groups `XX` / `XXX` / `XXX` |
| `slashKey` | `?string` | `'/'` | Separator before the branch block |
| `dashKey` | `?string` | `'-'` | Separator before the last two characters |
| `escape` | `?bool` | `false` | When `true`, HTML-escapes the final string (`HtmlUtils::escape`) |
| `encode` | `?bool` | `false` | When `true`, URL-encodes the final string (`UrlUtils::encodeUriComponent`) |
| `onFail` | `?\Closure` | see below | `Closure(mixed $value, CnpjFormatterException $e): string` — used when sanitized length ≠ 14 |

Default **`onFail`** returns an empty string. The exception passed for length failures is **`CnpjFormatterInputLengthException`** (`actualInput`, `evaluatedInput`, `expectedLength`).

### Errors & exceptions

- **Wrong input type** (not `string` or `list<string>`): **`CnpjFormatterInputTypeError`** — extends **`CnpjFormatterTypeError`** (extends PHP `TypeError`).
- **Invalid option types or values when constructing or merging options**: **`CnpjFormatterOptionsTypeError`**, **`CnpjFormatterOptionsHiddenRangeInvalidException`**, **`CnpjFormatterOptionsForbiddenKeyCharacterException`** — extend **`CnpjFormatterTypeError`** or **`CnpjFormatterException`** as appropriate.

Length mismatch does **not** throw from `format()`; handle it inside **`onFail`**.

```php
use Lacus\BrUtils\Cnpj\CnpjFormatter;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputLengthException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputTypeError;

try {
    (new CnpjFormatter())->format(12345);
} catch (CnpjFormatterInputTypeError $e) {
    echo $e->getMessage();
}

$out = (new CnpjFormatter())->format(
    'short',
    onFail: static fn ($value, CnpjFormatterInputLengthException $e) => 'invalid'
);
```

### Other available resources

- **`CNPJ_LENGTH`**: `14` — `CnpjFormatterOptions::CNPJ_LENGTH`, and global `Lacus\BrUtils\Cnpj\CNPJ_LENGTH` when the autoloaded `cnpj-fmt.php` file is loaded.
- **`CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS`**: Characters forbidden in `hiddenKey`, `dotKey`, `slashKey`, `dashKey`.
- **`CnpjFormatterOptions::getDefaultOnFail()`**: Shared default failure callback.

## Contribution & Support

We welcome contributions! Please see our [Contributing Guidelines](https://github.com/LacusSolutions/br-utils-php/blob/main/CONTRIBUTING.md) for details. If you find this project helpful, please consider:

- ⭐ Starring the repository
- 🤝 Contributing to the codebase
- 💡 [Suggesting new features](https://github.com/LacusSolutions/br-utils-php/issues)
- 🐛 [Reporting bugs](https://github.com/LacusSolutions/br-utils-php/issues)

## License

This project is licensed under the MIT License — see the [LICENSE](https://github.com/LacusSolutions/br-utils-php/blob/main/LICENSE) file for details.

## Changelog

See [CHANGELOG](https://github.com/LacusSolutions/br-utils-php/blob/main/packages/cnpj-fmt/CHANGELOG.md) for a list of changes and version history.

---

Made with ❤️ by [Lacus Solutions](https://github.com/LacusSolutions)
