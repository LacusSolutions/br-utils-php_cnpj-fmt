<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj;

use Closure;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputLengthException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputTypeError;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsForbiddenKeyCharacterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsHiddenRangeInvalidException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsTypeError;
use Lacus\Utils\HtmlUtils;
use Lacus\Utils\UrlUtils;

/**
 * Formatter for CNPJ (Cadastro Nacional da Pessoa Jurídica) identifiers. It
 * normalizes and optionally masks, HTML-escapes, or URL-encodes 14-character
 * alphanumeric CNPJ input. Accepts a string or array of strings;
 * non-alphanumeric characters are stripped and the result is uppercased.
 * Invalid input type is handled by throwing; invalid length is handled via the
 * configured `onFail` callback instead of throwing.
 */
class CnpjFormatter
{
    /**
     * A rarely-used 1-length character that is replaced with `hiddenKey` when
     * `hidden` is `true`.
     */
    private const HIDDEN_KEY_PLACEHOLDER = CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS[0];

    /**
     * The default options used by this formatter instance.
     */
    private readonly CnpjFormatterOptions $options;

    /**
     * Creates a new `CnpjFormatter` with optional default options.
     *
     * Default options apply to every call to `format` unless overridden by the
     * per-call `options` argument. Options control masking, HTML escaping, URL
     * encoding, and the callback used when formatting fails.
     *
     * When `defaultOptions` is a `CnpjFormatterOptions` instance, that instance
     * is used directly (no copy is created). Mutating it later (e.g. via the
     * `getOptions` return value or the original reference) affects future `format` calls
     * that do not pass per-call options. When a plain array or nothing is
     * passed, a new `CnpjFormatterOptions` instance is created from it.
     *
     * @param ?CnpjFormatterOptions $options
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
     *   option contains a disallowed character.
     */
    public function __construct(
        ?CnpjFormatterOptions $options = null,
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
    ) {
        $this->options = $options instanceof CnpjFormatterOptions
            ? $options
            : new CnpjFormatterOptions(
                hidden: $hidden,
                hiddenKey: $hiddenKey,
                hiddenStart: $hiddenStart,
                hiddenEnd: $hiddenEnd,
                dotKey: $dotKey,
                slashKey: $slashKey,
                dashKey: $dashKey,
                escape: $escape,
                encode: $encode,
                onFail: $onFail,
                overrides: [$options],
            );
    }

    /**
     * Returns the default options used by this formatter when per-call options
     * are not provided.
     *
     * The returned object is the same instance used internally; mutating it (e.g.
     * via setters on `CnpjFormatterOptions`) affects future `format` calls that
     * do not pass `options`.
     */
    public function getOptions(): CnpjFormatterOptions
    {
        return $this->options;
    }

    /**
     * Formats a CNPJ value into a normalized 14-character alphanumeric string.
     *
     * Input is normalized by stripping non-alphanumeric characters and converting
     * to uppercase. If the result length is not exactly 14, or if the input is
     * not a string or array of strings, the configured `onFail` callback is
     * invoked with the original value and an error; its return value is used as
     * the result.
     *
     * When valid, the result may be further transformed according to options:
     *
     * - If `hidden` is `true`, characters between `hiddenStart` and `hiddenEnd`
     *   (inclusive) are replaced with `hiddenKey`.
     * - If `escape` is `true`, HTML special characters are escaped.
     * - If `encode` is `true`, the string is passed through URL encoding (similar to
     *   JavaScript's `encodeURIComponent`).
     *
     * Per-call `options` are merged over the instance default options for this
     * call only; the instance defaults are unchanged.
     *
     * @param string|list<string> $cnpjInput
     * @param ?CnpjFormatterOptions $options
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
     *   option contains a disallowed character.
     * @throws CnpjFormatterInputTypeError If the input is not a string or array of strings.
     */
    public function format(
        $cnpjInput,
        $options = null,
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
    ): string {
        $actualInput = $this->toStringInput($cnpjInput);
        $actualOptions = new CnpjFormatterOptions(
            ...$this->options->getAll(),
            overrides: [
                [
                    'hidden' => $hidden,
                    'hiddenKey' => $hiddenKey,
                    'hiddenStart' => $hiddenStart,
                    'hiddenEnd' => $hiddenEnd,
                    'dotKey' => $dotKey,
                    'slashKey' => $slashKey,
                    'dashKey' => $dashKey,
                    'escape' => $escape,
                    'encode' => $encode,
                    'onFail' => $onFail,
                ],
                $options ?? [],
            ],
        );

        $alphanumericOnly = preg_replace('/[^0-9A-Za-z]/', '', $actualInput) ?? '';
        $formattedCnpj = strtoupper($alphanumericOnly);

        if (mb_strlen($formattedCnpj, 'UTF-8') !== CnpjFormatterOptions::CNPJ_LENGTH) {
            $exception = new CnpjFormatterInputLengthException(
                $cnpjInput,
                $formattedCnpj,
                CnpjFormatterOptions::CNPJ_LENGTH,
            );

            return ($actualOptions->onFail)($cnpjInput, $exception);
        }

        if ($actualOptions->hidden) {
            $startingPart = mb_substr($formattedCnpj, 0, $actualOptions->hiddenStart, 'UTF-8');
            $endingPart = mb_substr($formattedCnpj, $actualOptions->hiddenEnd + 1, null, 'UTF-8');
            $hiddenPartLength = $actualOptions->hiddenEnd - $actualOptions->hiddenStart + 1;
            $hiddenPart = str_repeat(self::HIDDEN_KEY_PLACEHOLDER, $hiddenPartLength);

            $formattedCnpj = $startingPart . $hiddenPart . $endingPart;
        }

        $formattedCnpj =
            mb_substr($formattedCnpj, 0, 2, 'UTF-8')
            . $actualOptions->dotKey
            . mb_substr($formattedCnpj, 2, 3, 'UTF-8')
            . $actualOptions->dotKey
            . mb_substr($formattedCnpj, 5, 3, 'UTF-8')
            . $actualOptions->slashKey
            . mb_substr($formattedCnpj, 8, 4, 'UTF-8')
            . $actualOptions->dashKey
            . mb_substr($formattedCnpj, 12, 2, 'UTF-8');
        $formattedCnpj = str_replace(
            self::HIDDEN_KEY_PLACEHOLDER,
            $actualOptions->hiddenKey,
            $formattedCnpj,
        );

        if ($actualOptions->escape) {
            $formattedCnpj = HtmlUtils::escape($formattedCnpj);
        }

        if ($actualOptions->encode) {
            $formattedCnpj = UrlUtils::encodeUriComponent($formattedCnpj);
        }

        return $formattedCnpj;
    }

    /**
     * Normalizes the input to a string.
     *
     * @param mixed $cnpjInput
     *
     * @throws CnpjFormatterInputTypeError If the input is not a string or array
     *   of strings.
     */
    private function toStringInput(mixed $cnpjInput): string
    {
        if (is_string($cnpjInput)) {
            return $cnpjInput;
        }

        if (is_array($cnpjInput)) {
            $joined = '';

            foreach ($cnpjInput as $item) {
                if (!is_string($item)) {
                    throw new CnpjFormatterInputTypeError($cnpjInput, 'string or string[]');
                }

                $joined .= $item;
            }

            return $joined;
        }

        throw new CnpjFormatterInputTypeError($cnpjInput, 'string or string[]');
    }
}
