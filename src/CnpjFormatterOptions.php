<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj;

use Closure;
use InvalidArgumentException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsForbiddenKeyCharacterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsHiddenRangeInvalidException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsTypeError;

/**
 * Class to store the options for the CNPJ formatter. This class provides a
 * centralized way to configure how CNPJ numbers are formatted, including
 * delimiters, hidden character ranges, HTML escaping, URL encoding, and error
 * handling callbacks.
 *
 * @property bool $hidden
 * @property string $hiddenKey
 * @property int $hiddenStart
 * @property int $hiddenEnd
 * @property string $dotKey
 * @property string $slashKey
 * @property string $dashKey
 * @property bool $escape
 * @property bool $encode
 * @property Closure(mixed, CnpjFormatterException): string $onFail
 */
class CnpjFormatterOptions
{
    /**
     * The standard length of a CNPJ (Cadastro Nacional da Pessoa Jurídica)
     * identifier (14 alphanumeric characters).
     */
    public const CNPJ_LENGTH = 14;

    /**
     * Minimum valid index for the hidden range (inclusive). Must be between 0 and
     * CNPJ_LENGTH - 1.
     */
    private const MIN_HIDDEN_RANGE = 0;

    /**
     * Maximum valid index for the hidden range (inclusive). Must be between 0 and
     * CNPJ_LENGTH - 1.
     */
    private const MAX_HIDDEN_RANGE = self::CNPJ_LENGTH - 1;

    /**
     * Default value for the `hidden` option. When `false`, all CNPJ characters
     * are displayed.
     */
    public const DEFAULT_HIDDEN = false;

    /**
     * Default string used to replace hidden CNPJ characters.
     */
    public const DEFAULT_HIDDEN_KEY = '*';

    /**
     * Default start index (inclusive) for hiding CNPJ characters. Characters from
     * this index onwards will be replaced with the `hiddenKey` value.
     */
    public const DEFAULT_HIDDEN_START = 5;

    /**
     * Default end index (inclusive) for hiding CNPJ characters. Characters up to
     * and including this index will be replaced with the `hiddenKey` value.
     */
    public const DEFAULT_HIDDEN_END = 13;

    /**
     * Default string used as the dot delimiter in formatted CNPJ. Used to
     * separate the first groups of characters (XX.XXX.XXX).
     */
    public const DEFAULT_DOT_KEY = '.';

    /**
     * Default string used as the slash delimiter in formatted CNPJ. Used to
     * separate the first group of characters from the branch identifier
     * (XXXXXXXX/XXXX).
     */
    public const DEFAULT_SLASH_KEY = '/';

    /**
     * Default string used as the dash delimiter in formatted CNPJ. Used to
     * separate the branch identifier from the check digits at the end (XXXX-XX).
     */
    public const DEFAULT_DASH_KEY = '-';

    /**
     * Default value for the `escape` option. When `false`, HTML special
     * characters are not escaped.
     */
    public const DEFAULT_ESCAPE = false;

    /**
     * Default value for the `encode` option. When `false`, the CNPJ string is not
     * URL-encoded.
     */
    public const DEFAULT_ENCODE = false;

    /**
     * @var (Closure(mixed, CnpjFormatterException): string)|null
     */
    private static ?Closure $defaultOnFailCallback = null;

    /**
     * @return Closure(mixed, CnpjFormatterException): string
     */
    public static function getDefaultOnFail(): Closure
    {
        if (self::$defaultOnFailCallback === null) {
            self::$defaultOnFailCallback = static fn (mixed $value, CnpjFormatterException $exception): string => '';
        }

        return self::$defaultOnFailCallback;
    }

    /**
     * Characters that are not allowed in key options (`hiddenKey`, `dotKey`,
     * `slashKey`, `dashKey`). They are reserved for internal formatting logic.
     *
     * For now, it's only used to replace the hidden key placeholder in the
     * CnpjFormatter class. However, this set of characters is reserved for future
     * use already.
     *
     * @var list<string>
     */
    public const DISALLOWED_KEY_CHARACTERS = [
        "\u{00e5}",
        "\u{00eb}",
        "\u{00ef}",
        "\u{00f6}",
    ];

    /**
     * @var array{
     *     hidden: bool,
     *     hiddenKey: string,
     *     hiddenStart: int,
     *     hiddenEnd: int,
     *     dotKey: string,
     *     slashKey: string,
     *     dashKey: string,
     *     escape: bool,
     *     encode: bool,
     *     onFail: Closure(mixed, CnpjFormatterException): string
     * }
     */
    private array $options = []; // @phpstan-ignore-line property.defaultValue

    /**
     * Creates a new instance of `CnpjFormatterOptions`.
     *
     * Options can be provided in multiple ways:
     *
     * 1. As a single options array or another `CnpjFormatterOptions` instance.
     * 2. As multiple override objects that are merged in order (later overrides take
     *    precedence)
     *
     * All options are optional and will default to their predefined values if not
     * provided. The `hiddenStart` and `hiddenEnd` options are validated to ensure
     * they are within the valid range [0, CNPJ_LENGTH - 1] and will be swapped if
     * `hiddenStart > hiddenEnd`.
     *
     * @param ?bool $hidden
     * @param ?string $hiddenKey
     * @param ?int $hiddenStart
     * @param ?int $hiddenEnd
     * @param ?string $dotKey
     * @param ?string $slashKey
     * @param ?string $dashKey
     * @param ?bool $escape
     * @param ?bool $encode
     * @param ?Closure(mixed, CnpjFormatterException): string $onFail
     * @param list<CnpjFormatterOptions|array{
     *     hidden?: bool|null,
     *     hiddenKey?: string|null,
     *     hiddenStart?: int|null,
     *     hiddenEnd?: int|null,
     *     dotKey?: string|null,
     *     slashKey?: string|null,
     *     dashKey?: string|null,
     *     escape?: bool|null,
     *     encode?: bool|null,
     *     onFail?: (Closure(mixed, CnpjFormatterException): string|null)
     * }|null> $overrides
     *
     * @throws CnpjFormatterOptionsTypeError If any option has an invalid type.
     * @throws CnpjFormatterOptionsHiddenRangeInvalidException If `hiddenStart`
     *   or `hiddenEnd` are out of valid range.
     * @throws CnpjFormatterOptionsForbiddenKeyCharacterException If any key
     *   option (`hiddenKey`, `dotKey`, `slashKey`, `dashKey`) contains a
     *   disallowed character.
     */
    public function __construct(
        $hidden = null,
        $hiddenKey = null,
        $hiddenStart = null,
        $hiddenEnd = null,
        $dotKey = null,
        $slashKey = null,
        $dashKey = null,
        $escape = null,
        $encode = null,
        $onFail = null,
        ?array $overrides = [],
    ) {
        $this->setHidden($hidden);
        $this->setHiddenKey($hiddenKey);
        $this->setHiddenRange($hiddenStart, $hiddenEnd);
        $this->setDotKey($dotKey);
        $this->setSlashKey($slashKey);
        $this->setDashKey($dashKey);
        $this->setEscape($escape);
        $this->setEncode($encode);
        $this->setOnFail($onFail);

        foreach (($overrides ?? []) as $override) {
            if ($override === null) {
                continue;
            }

            if ($override instanceof CnpjFormatterOptions) {
                $this->set(...$override->getAll());
            } elseif (is_array($override)) {
                $this->set(
                    hidden: $override['hidden'] ?? null,
                    hiddenKey: $override['hiddenKey'] ?? null,
                    hiddenStart: $override['hiddenStart'] ?? null,
                    hiddenEnd: $override['hiddenEnd'] ?? null,
                    dotKey: $override['dotKey'] ?? null,
                    slashKey: $override['slashKey'] ?? null,
                    dashKey: $override['dashKey'] ?? null,
                    escape: $override['escape'] ?? null,
                    encode: $override['encode'] ?? null,
                    onFail: $override['onFail'] ?? null,
                );
            }
        }
    }

    /**
     * Property-style access to the options.
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'hidden'      => $this->getHidden(),
            'hiddenKey'   => $this->getHiddenKey(),
            'hiddenStart' => $this->getHiddenStart(),
            'hiddenEnd'   => $this->getHiddenEnd(),
            'dotKey'      => $this->getDotKey(),
            'slashKey'    => $this->getSlashKey(),
            'dashKey'     => $this->getDashKey(),
            'escape'      => $this->getEscape(),
            'encode'      => $this->getEncode(),
            'onFail'      => $this->getOnFail(),
            default       => throw new InvalidArgumentException("Unknown property: {$name}"),
        };
    }

    /**
     * Property-style mutation to the options.
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'hidden'      => $this->setHidden($value),      // @phpstan-ignore-line argument.type
            'hiddenKey'   => $this->setHiddenKey($value),   // @phpstan-ignore-line method.notFound
            'hiddenStart' => $this->setHiddenStart($value), // @phpstan-ignore-line method.notFound
            'hiddenEnd'   => $this->setHiddenEnd($value),   // @phpstan-ignore-line method.notFound
            'dotKey'      => $this->setDotKey($value),      // @phpstan-ignore-line method.notFound
            'slashKey'    => $this->setSlashKey($value),    // @phpstan-ignore-line method.notFound
            'dashKey'     => $this->setDashKey($value),     // @phpstan-ignore-line method.notFound
            'escape'      => $this->setEscape($value),      // @phpstan-ignore-line argument.type
            'encode'      => $this->setEncode($value),      // @phpstan-ignore-line argument.type
            'onFail'      => $this->setOnFail($value),      // @phpstan-ignore-line argument.type
            default       => throw new InvalidArgumentException("Unknown property: {$name}"),
        };
    }

    /**
     * Returns a shallow copy of all current options. This is useful for creating
     * snapshots of the current configuration.
     *
     * @return array{
     *     hidden: bool,
     *     hiddenKey: string,
     *     hiddenStart: int,
     *     hiddenEnd: int,
     *     dotKey: string,
     *     slashKey: string,
     *     dashKey: string,
     *     escape: bool,
     *     encode: bool,
     *     onFail: Closure(mixed, CnpjFormatterException): string
     * }
     */
    public function getAll(): array
    {
        return [...$this->options];
    }

    /**
     * Gets whether hidden character replacement is enabled. When `true`,
     * characters within the `hiddenStart` to `hiddenEnd` range will be replaced
     * with the `hiddenKey` character.
     */
    private function getHidden(): bool
    {
        return $this->options['hidden'];
    }

    /**
     * Sets whether hidden character replacement is enabled. When set to `true`,
     * characters within the `hiddenStart` to `hiddenEnd` range will be replaced
     * with the `hiddenKey` character. The value is converted to a boolean, so
     * truthy/falsy values are handled appropriately.
     *
     * @param bool|null $value
     */
    private function setHidden($value): void
    {
        $actualHidden = $value ?? self::DEFAULT_HIDDEN;
        $actualHidden = (bool) $actualHidden;

        $this->options['hidden'] = $actualHidden;
    }

    /**
     * Gets the string used to replace hidden CNPJ characters. This string is used
     * when `hidden` is `true` to mask characters in the range from `hiddenStart`
     * to `hiddenEnd` (inclusive).
     */
    private function getHiddenKey(): string
    {
        return $this->options['hiddenKey'];
    }

    /**
     * Sets the string used to replace hidden CNPJ characters. This string is used
     * when `hidden` is `true` to mask characters in the range from `hiddenStart`
     * to `hiddenEnd` (inclusive).
     *
     * @param string|null $value
     *
     * @throws CnpjFormatterOptionsTypeError If the value is not a string.
     * @throws CnpjFormatterOptionsForbiddenKeyCharacterException If the value
     *   contains any disallowed key character.
     */
    private function setHiddenKey($value): void
    {
        $actualHiddenKey = $value ?? self::DEFAULT_HIDDEN_KEY;

        $this->assertIsString('hiddenKey', $actualHiddenKey);
        $this->assertHasAllowedCharacters('hiddenKey', $actualHiddenKey);

        $this->options['hiddenKey'] = $actualHiddenKey;
    }

    /**
     * Gets the start index (inclusive) for hiding CNPJ characters. This is the
     * first position in the CNPJ string where characters will be replaced with
     * the `hiddenKey` string when `hidden` is `true`. Must be between `0` and
     * `13` (`CNPJ_LENGTH - 1`).
     */
    private function getHiddenStart(): int
    {
        return $this->options['hiddenStart'];
    }

    /**
     * Sets the start index (inclusive) for hiding CNPJ characters. This is the
     * first position in the CNPJ string where characters will be replaced with
     * the `hiddenKey` when `hidden` is `true`. The value is validated and will be
     * swapped with `hiddenEnd` if necessary to ensure `hiddenStart <= hiddenEnd`.
     *
     * @param int|null $value
     *
     * @throws CnpjFormatterOptionsTypeError If the value is not an integer.
     * @throws CnpjFormatterOptionsHiddenRangeInvalidException If `hiddenStart`
     *   or `hiddenEnd` are out of valid range.
     */
    private function setHiddenStart($value): void
    {
        $this->setHiddenRange($value, $this->options['hiddenEnd']);
    }

    /**
     * Gets the end index (inclusive) for hiding CNPJ characters. This is the last
     * position in the CNPJ string where characters will be replaced with the
     * `hiddenKey` string when `hidden` is `true`. Must be between `0` and `13`
     * (`CNPJ_LENGTH - 1`).
     */
    private function getHiddenEnd(): int
    {
        return $this->options['hiddenEnd'];
    }

    /**
     * Sets the end index (inclusive) for hiding CNPJ characters. This is the last
     * position in the CNPJ string where characters will be replaced with the
     * `hiddenKey` when `hidden` is `true`. The value is validated and will be
     * swapped with `hiddenStart` if necessary to ensure `hiddenStart <=
     * hiddenEnd`.
     *
     * @param int|null $value
     *
     * @throws CnpjFormatterOptionsTypeError If the value is not an integer.
     * @throws CnpjFormatterOptionsHiddenRangeInvalidException If `hiddenStart`
     *   or `hiddenEnd` are out of valid range.
     */
    private function setHiddenEnd($value): void
    {
        $this->setHiddenRange($this->options['hiddenStart'], $value);
    }

    /**
     * Gets the string used as the dot delimiter. This string is used to separate
     * the first groups of characters in the formatted CNPJ (e.g., `"."` in
     * "12.345.678/0001-90").
     */
    private function getDotKey(): string
    {
        return $this->options['dotKey'];
    }

    /**
     * Sets the string used as the dot delimiter. This string is used to separate
     * the first groups of characters in the formatted CNPJ (e.g., `"."` in
     * `"12.345.678/0001-90"`).
     *
     * @param string|null $value
     *
     * @throws CnpjFormatterOptionsTypeError If the value is not a string.
     * @throws CnpjFormatterOptionsForbiddenKeyCharacterException If the value
     *   contains any disallowed key character.
     */
    private function setDotKey($value): void
    {
        $actualDotKey = $value ?? self::DEFAULT_DOT_KEY;

        $this->assertIsString('dotKey', $actualDotKey);
        $this->assertHasAllowedCharacters('dotKey', $actualDotKey);

        $this->options['dotKey'] = $actualDotKey;
    }

    /**
     * Gets the string used as the slash delimiter. This string is used to
     * separate the first group of characters from the branch identifier in the
     * formatted CNPJ (e.g., `"/"` in `"12.345.678/0001-90"`).
     */
    private function getSlashKey(): string
    {
        return $this->options['slashKey'];
    }

    /**
     * Sets the string used as the slash delimiter. This string is used to
     * separate the first group of characters from the branch identifier in the
     * formatted CNPJ (e.g., `"/"` in `"12.345.678/0001-90"`).
     *
     * @param string|null $value
     *
     * @throws CnpjFormatterOptionsTypeError If the value is not a string.
     * @throws CnpjFormatterOptionsForbiddenKeyCharacterException If the value
     *   contains any disallowed key character.
     */
    private function setSlashKey($value): void
    {
        $actualSlashKey = $value ?? self::DEFAULT_SLASH_KEY;

        $this->assertIsString('slashKey', $actualSlashKey);
        $this->assertHasAllowedCharacters('slashKey', $actualSlashKey);

        $this->options['slashKey'] = $actualSlashKey;
    }

    /**
     * Gets the string used as the dash delimiter. This string is used to separate
     * the check digits at the end in the formatted CNPJ (e.g., `"-"` in
     * `"12.345.678/0001-90"`).
     */
    private function getDashKey(): string
    {
        return $this->options['dashKey'];
    }

    /**
     * Sets the string used as the dash delimiter. This string is used to separate
     * the check digits at the end in the formatted CNPJ (e.g., `"-"` in
     * `"12.345.678/0001-90"`).
     *
     * @param string|null $value
     *
     * @throws CnpjFormatterOptionsTypeError If the value is not a string.
     * @throws CnpjFormatterOptionsForbiddenKeyCharacterException If the value
     *   contains any disallowed key character.
     */
    private function setDashKey($value): void
    {
        $actualDashKey = $value ?? self::DEFAULT_DASH_KEY;

        $this->assertIsString('dashKey', $actualDashKey);
        $this->assertHasAllowedCharacters('dashKey', $actualDashKey);

        $this->options['dashKey'] = $actualDashKey;
    }

    /**
     * Gets whether HTML escaping is enabled. When `true`, HTML special characters
     * (like `<`, `>`, `&`, etc.) in the formatted CNPJ string will be escaped.
     * This is useful when using custom delimiters that may contain HTML
     * characters or when displaying CNPJ in HTML.
     */
    private function getEscape(): bool
    {
        return $this->options['escape'];
    }

    /**
     * Sets whether HTML escaping is enabled. When set to `true`, HTML special
     * characters (like `<`, `>`, `&`, etc.) in the formatted CNPJ string will be
     * escaped. This is useful when using custom delimiters that may contain HTML
     * characters or when displaying CNPJ in HTML. The value is converted to a
     * boolean, so truthy/falsy values are handled appropriately.
     *
     * @param bool|null $value
     */
    private function setEscape($value): void
    {
        $actualEscape = $value ?? self::DEFAULT_ESCAPE;
        $actualEscape = (bool) $actualEscape;

        $this->options['escape'] = $actualEscape;
    }

    /**
     * Gets whether URL encoding is enabled. When `true`, the formatted CNPJ
     * string will be URL-encoded, making it safe to use in URL query parameters
     * or path segments.
     */
    private function getEncode(): bool
    {
        return $this->options['encode'];
    }

    /**
     * Sets whether URL encoding is enabled. When set to `true`, the formatted
     * CNPJ string will be URL-encoded, making it safe to use in URL query
     * parameters or path segments. The value is converted to a boolean, so
     * truthy/falsy values are handled appropriately.
     *
     * @param bool|null $value
     */
    private function setEncode($value): void
    {
        $actualEncode = $value ?? self::DEFAULT_ENCODE;
        $actualEncode = (bool) $actualEncode;

        $this->options['encode'] = $actualEncode;
    }

    /**
     * Gets the callback function executed when formatting fails. This function is
     * called when the formatter encounters an error (e.g., invalid input, invalid
     * options). It receives the input value and an exception object, and
     * should return a string to use as the fallback output.
     *
     * @return Closure(mixed, CnpjFormatterException): string
     */
    private function getOnFail(): Closure
    {
        return $this->options['onFail'];
    }

    /**
     * Sets the callback function executed when formatting fails. This function is
     * called when the formatter encounters an error (e.g., invalid input, invalid
     * options). It receives the input value and an exception object, and
     * should return a string to use as the fallback output.
     *
     * @param (Closure(mixed, CnpjFormatterException): string)|null $value
     *
     * @throws CnpjFormatterOptionsTypeError If the value is not a Closure.
     */
    private function setOnFail($value): void
    {
        $actualOnFail = $value ?? self::getDefaultOnFail();

        $this->assertIsClosure('onFail', $actualOnFail);

        $this->options['onFail'] = $actualOnFail;
    }

    /**
     * Sets the hiddenStart and hiddenEnd options with proper validation and
     * sanitization. This method validates that both indices are integers within
     * the valid range [`0`, `CNPJ_LENGTH - 1`]. If `hiddenStart > hiddenEnd`, the
     * values are automatically swapped to ensure a valid range. This method is
     * used internally by the `hiddenStart` and `hiddenEnd` setters to maintain
     * consistency.
     *
     * @param int|null $hiddenStart
     * @param int|null $hiddenEnd
     *
     * @throws CnpjFormatterOptionsTypeError If either value is not an integer.
     * @throws CnpjFormatterOptionsHiddenRangeInvalidException If `hiddenStart`
     *   or `hiddenEnd` are out of valid range.
     */
    public function setHiddenRange($hiddenStart, $hiddenEnd): self
    {
        $actualHiddenStart = $hiddenStart ?? self::DEFAULT_HIDDEN_START;
        $actualHiddenEnd = $hiddenEnd ?? self::DEFAULT_HIDDEN_END;

        $this->assertIsInt('hiddenStart', $actualHiddenStart);
        $this->assertIsInt('hiddenEnd', $actualHiddenEnd);
        $this->assertIsBetweenHiddenRangeBounds('hiddenStart', $actualHiddenStart);
        $this->assertIsBetweenHiddenRangeBounds('hiddenEnd', $actualHiddenEnd);

        if ($actualHiddenStart > $actualHiddenEnd) {
            [$actualHiddenStart, $actualHiddenEnd] = [$actualHiddenEnd, $actualHiddenStart];
        }

        $this->options['hiddenStart'] = $actualHiddenStart;
        $this->options['hiddenEnd'] = $actualHiddenEnd;

        return $this;
    }

    /**
     * Sets multiple options at once. This method allows you to update multiple
     * options in a single call. Only the provided options are updated; options
     * not included in the object retain their current values. You can pass either
     * a partial options array or another `CnpjFormatterOptions` instance.
     *
     * @param ?bool $hidden
     * @param ?string $hiddenKey
     * @param ?int $hiddenStart
     * @param ?int $hiddenEnd
     * @param ?string $dotKey
     * @param ?string $slashKey
     * @param ?string $dashKey
     * @param ?bool $escape
     * @param ?bool $encode
     * @param ?Closure(mixed, CnpjFormatterException): string $onFail
     *
     * @throws CnpjFormatterOptionsTypeError If any option has an invalid type.
     * @throws CnpjFormatterOptionsHiddenRangeInvalidException If `hiddenStart`
     *   or `hiddenEnd` are out of valid range.
     * @throws CnpjFormatterOptionsForbiddenKeyCharacterException If any key
     *   option (`hiddenKey`, `dotKey`, `slashKey`, `dashKey`) contains a
     *   disallowed character.
     */
    public function set(
        $hidden = null,
        $hiddenKey = null,
        $hiddenStart = null,
        $hiddenEnd = null,
        $dotKey = null,
        $slashKey = null,
        $dashKey = null,
        $escape = null,
        $encode = null,
        $onFail = null,
    ): self {
        $this->setHidden($hidden ?? $this->getHidden());
        $this->setHiddenKey($hiddenKey ?? $this->getHiddenKey());
        $this->setDotKey($dotKey ?? $this->getDotKey());
        $this->setHiddenRange($hiddenStart ?? $this->getHiddenStart(), $hiddenEnd ?? $this->getHiddenEnd());
        $this->setSlashKey($slashKey ?? $this->getSlashKey());
        $this->setDashKey($dashKey ?? $this->getDashKey());
        $this->setEscape($escape ?? $this->getEscape());
        $this->setEncode($encode ?? $this->getEncode());
        $this->setOnFail($onFail ?? $this->getOnFail());

        return $this;
    }

    /**
     * Throws if the given value is not a string.
     *
     * @param 'hiddenKey'|'dotKey'|'slashKey'|'dashKey' $optionName
     *
     * @throws CnpjFormatterOptionsTypeError If the option value is not a string.
     */
    private function assertIsString(string $optionName, mixed $value): void
    {
        if (!is_string($value)) {
            throw new CnpjFormatterOptionsTypeError($optionName, $value, 'string');
        }
    }

    /**
     * Throws if the given string contains any disallowed key character.
     *
     * @param 'hiddenKey'|'dotKey'|'slashKey'|'dashKey' $optionName
     *
     * @throws CnpjFormatterOptionsForbiddenKeyCharacterException If `value`
     *   contains any character from `DISALLOWED_KEY_CHARACTERS`.
     */
    private function assertHasAllowedCharacters(string $optionName, string $value): void
    {
        $forbiddenChars = self::DISALLOWED_KEY_CHARACTERS;

        foreach ($forbiddenChars as $ch) {
            if (str_contains($value, $ch)) {
                throw new CnpjFormatterOptionsForbiddenKeyCharacterException(
                    $optionName,
                    $value,
                    $forbiddenChars,
                );
            }
        }
    }

    /**
     * Throws if the given value is not an integer.
     *
     * @param 'hiddenStart'|'hiddenEnd' $optionName
     *
     * @throws CnpjFormatterOptionsTypeError If the option value is not an integer.
     */
    private function assertIsInt(string $optionName, mixed $value): void
    {
        if (!is_int($value)) {
            throw new CnpjFormatterOptionsTypeError($optionName, $value, 'integer');
        }
    }

    /**
     * Throws if the given value is not between the hidden range bounds.
     *
     * @param 'hiddenStart'|'hiddenEnd' $optionName
     *
     * @throws CnpjFormatterOptionsHiddenRangeInvalidException If `hiddenStart`
     *   or `hiddenEnd` are out of valid range.
     */
    private function assertIsBetweenHiddenRangeBounds(string $optionName, int $value): void
    {
        if ($value < self::MIN_HIDDEN_RANGE || $value > self::MAX_HIDDEN_RANGE) {
            throw new CnpjFormatterOptionsHiddenRangeInvalidException(
                $optionName,
                $value,
                self::MIN_HIDDEN_RANGE,
                self::MAX_HIDDEN_RANGE,
            );
        }
    }

    /**
     * Throws if the given value is not a Closure.
     *
     * @param 'onFail' $optionName
     *
     * @throws CnpjFormatterOptionsTypeError If the option value is not a Closure.
     */
    private function assertIsClosure(string $optionName, mixed $value): void
    {
        if (!$value instanceof Closure) {
            throw new CnpjFormatterOptionsTypeError($optionName, $value, 'function');
        }
    }
}
