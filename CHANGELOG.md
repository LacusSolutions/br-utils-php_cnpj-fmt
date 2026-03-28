# lacus/cnpj-fmt

## 2.0.0

### 🎉 v2 at a glance 🎊

- 🆕 **Alphanumeric CNPJ** — Full support for the new [14-character alphanumeric CNPJ](https://www.gov.br/receitafederal/pt-br/assuntos/noticias/2023/julho/cnpj-alfa-numerico) (digits and letters); input is sanitized and uppercased before formatting.
- 🛡️ **Structured errors** — Typed exceptions (`CnpjFormatterTypeError`, `CnpjFormatterException` and their subclasses variants) for clearer error handling.

### BREAKING CHANGES

- **Letters no longer stripped** from the input. With the new alphanumeric CNPJ format, letters are kept in the input sanitization and validated on the length of processed data.
- **Namespace**: In the process of normalizing the namespaces of **BR Utils** resources, the package's public API moved from `Lacus\CnpjFmt\` to `Lacus\BrUtils\Cnpj\`. Therefore update `use` statements and autoload expectations for `CnpjFormatter`, `CnpjFormatterOptions`, and `cnpj_fmt` accordingly.
- **Drop support to PHP v8.1**: Minimum version for the package is now **PHP 8.2** (`^8.2`). It may even run forcedly in earlier versions, but it's not recommended to keep running stale versions of PHP in production.
- **Input type**: `CnpjFormatter::format()` and `cnpj_fmt()` accept a **string or a list of strings** (arrays are concatenated). Passing a non-string / non–`string[]` value throws **`CnpjFormatterInputTypeError`**. Prior major version only accepted `string`, so no actual change is really needed in this topic.
- **`onFail` callback** signature is now `Closure(mixed $value, CnpjFormatterException $exception): string`. The default implementation returns an **empty string** on failure; v1 defaulted to returning the **original input string** for invalid length. Length failures are now represented by **`CnpjFormatterInputLengthException`** (not `InvalidArgumentException`).
- **`CnpjFormatterOptions::merge()`** method no longer exists. Now, to create a new version of `CnpjFormatterOptions` merged with other customized options, just construct a isntance of the class passing the argument **`overrides`**, which accepts an array of options, with the reference instance and the attributes you want to override.
- **Options of `CnpjFormatterOptions`** are now accessible as properties, instead of getters and setters.
- Migrated tests from PhpUnit to **Pest**.

### New features

- **Alphanumeric CNPJ**: Full support for the new alphanumeric CNPJ format (14 characters from `0`–`9` and `A`–`Z` after normalization).
- **`encode` option**: Optional URL encoding of the formatted CNPJ (via `lacus/utils` `UrlUtils::encodeUriComponent`), similar in spirit to `encodeURIComponent`.
- **HTML escaping**: `escape` uses `HtmlUtils::escape` from **`lacus/utils`** instead of `htmlspecialchars` directly.
- **`CnpjFormatter` constructor**: Optional first argument can be a **`CnpjFormatterOptions`** instance (shared by reference), or options can be passed as named parameters; v1 only accepted flat option parameters in a fixed order.
- **`format()` per-call options**: Second argument may be a **`CnpjFormatterOptions`** instance or an associative array, merged with named parameters over instance defaults.
- **Explicit error model**: `CnpjFormatterTypeError` / `CnpjFormatterException` hierarchies and concrete classes (`CnpjFormatterInputTypeError`, `CnpjFormatterOptionsTypeError`, `CnpjFormatterOptionsHiddenRangeInvalidException`, `CnpjFormatterOptionsForbiddenKeyCharacterException`, etc.) for typed errors and clearer handling.
- **`CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS`**: Reserved characters for `hiddenKey`, `dotKey`, `slashKey`, and `dashKey` (internal masking pipeline).
- **`CnpjFormatterOptions::getDefaultOnFail()`**: Shared default failure callback.

### Improvements

- **New PT-BR documentation**: New [README in Brazilian Portuguese](./README.pt.md).

## 1.0.0

### Stable v1 API

The first major release of **lacus/cnpj-fmt** under namespace **`Lacus\CnpjFmt`**, focused on **numeric** CNPJ formatting (14 digits).

- **`CnpjFormatter`**: Formats a CNPJ string into the usual `XX.XXX.XXX/XXXX-XX` pattern (with configurable separators).
- **`CnpjFormatterOptions`**: Options for `escape`, `hidden`, `hiddenKey`, `hiddenStart`, `hiddenEnd`, `dotKey`, `slashKey`, `dashKey`, and `onFail`; **`merge()`** for per-call overrides from `format()`.
- **`cnpj_fmt()`**: Helper that instantiates `CnpjFormatter` and calls `format()` with the same option parameters.
- **`CNPJ_LENGTH`**: Global constant `14` in `cnpj-fmt.php` (aligned with `Lacus\CnpjFmt` autoload).
- **Invalid length**: Invoked `onFail` with **`InvalidArgumentException`** as the second argument; default callback returned the **original input string**.
- **Numeric-only input**: Stripped non-digits; required exactly **14 digits** after stripping.
- **PHP**: PHP **≥ 8.1**; no `lacus/utils` requirement.
- **Tests**: PHPUnit with shared test cases trait.
