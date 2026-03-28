![cnpj-fmt for PHP](https://br-utils.vercel.app/img/cover_cnpj-fmt.jpg)

[![Packagist Version](https://img.shields.io/packagist/v/lacus/cnpj-fmt)](https://packagist.org/packages/lacus/cnpj-fmt)
[![Packagist Downloads](https://img.shields.io/packagist/dm/lacus/cnpj-fmt)](https://packagist.org/packages/lacus/cnpj-fmt)
[![PHP Version](https://img.shields.io/packagist/php-v/lacus/cnpj-fmt)](https://www.php.net/)
[![Test Status](https://img.shields.io/github/actions/workflow/status/LacusSolutions/br-utils-php/ci.yml?label=ci/cd)](https://github.com/LacusSolutions/br-utils-php/actions)
[![Last Update Date](https://img.shields.io/github/last-commit/LacusSolutions/br-utils-php)](https://github.com/LacusSolutions/br-utils-php)
[![Project License](https://img.shields.io/github/license/LacusSolutions/br-utils-php)](https://github.com/LacusSolutions/br-utils-php/blob/main/LICENSE)

Utility function/class to format CNPJ (Brazilian employer ID).

## PHP Support

| ![PHP 8.1](https://img.shields.io/badge/PHP-8.1-777BB4?logo=php&logoColor=white) | ![PHP 8.2](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white) | ![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white) | ![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white) |
|--- | --- | --- | --- |
| Passing ✔ | Passing ✔ | Passing ✔ | Passing ✔ |

## Installation

```bash
# using Composer
$ composer require lacus/cnpj-fmt
```

## Import

```php
<?php
// Using class-based resource
use Lacus\CnpjFmt\CnpjFormatter;

// Or using function-based one
use function Lacus\CnpjFmt\cnpj_fmt;
```

## Usage

### Object-Oriented Usage

```php
$formatter = new CnpjFormatter();
$cnpj = '03603568000195';

echo $formatter->format($cnpj);       // returns '03.603.568/0001-95'

// With options
echo $formatter->format(
    $cnpj,
    hidden: true,
    hiddenKey: '#',
    hiddenStart: 5,
    hiddenEnd: 13
);  // returns '03.603.###/####-##'
```

The options can be provided to the constructor or the `format()` method. If passed to the constructor, the options will be attached to the `CnpjFormatter` instance. When passed to the `format()` method, it only applies the options to that specific call.

```php
$cnpj = '03603568000195';
$formatter = new CnpjFormatter(hidden: true);

echo $formatter->format($cnpj);                  // '03.603.***/****-**'
echo $formatter->format($cnpj, hidden: false);   // '03.603.568/0001-95' merges the options to the instance's
echo $formatter->format($cnpj);                  // '03.603.***/****-**' uses only the instance options
```

### Imperative programming

The helper function `cnpj_fmt()` is just a functional abstraction. Internally it creates an instance of `CnpjFormatter` and calls the `format()` method right away.

```php
$cnpj = '03603568000195';

echo cnpj_fmt($cnpj);       // returns '03.603.568/0001-95'

echo cnpj_fmt($cnpj, hidden: true);     // returns '03.603.***/****-**'

echo cnpj_fmt($cnpj, dotKey: '', slashKey: '|', dashKey: '_');     // returns '03603568|0001_95'
```

### Formatting Options

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `escape` | `?bool` | `false` | Whether to HTML escape the result |
| `hidden` | `?bool` | `false` | Whether to hide digits with a mask |
| `hiddenKey` | `?string` | `'*'` | Character to replace hidden digits |
| `hiddenStart` | `?int` | `5` | Starting index for hidden range (0-13) |
| `hiddenEnd` | `?int` | `13` | Ending index for hidden range (0-13) |
| `dotKey` | `?string` | `'.'` | String to replace dot characters |
| `slashKey` | `?string` | `'/'` | String to replace slash character |
| `dashKey` | `?string` | `'-'` | String to replace dash character |
| `onFail` | `?callable` | `fn($v) => $v` | Fallback function for invalid input |

## Contribution & Support

We welcome contributions! Please see our [Contributing Guidelines](https://github.com/LacusSolutions/br-utils-php/blob/main/CONTRIBUTING.md) for details. But if you find this project helpful, please consider:

- ⭐ Starring the repository
- 🤝 Contributing to the codebase
- 💡 [Suggesting new features](https://github.com/LacusSolutions/br-utils-php/issues)
- 🐛 [Reporting bugs](https://github.com/LacusSolutions/br-utils-php/issues)

## License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/LacusSolutions/br-utils-php/blob/main/LICENSE) file for details.

## Changelog

See [CHANGELOG](https://github.com/LacusSolutions/br-utils-php/blob/main/packages/cnpj-fmt/CHANGELOG.md) for a list of changes and version history.

---

Made with ❤️ by [Lacus Solutions](https://github.com/LacusSolutions)
