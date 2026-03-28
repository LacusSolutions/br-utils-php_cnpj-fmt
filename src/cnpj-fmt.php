<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj;

use Closure;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterException;

/**
 * The standard length of a CNPJ (Cadastro Nacional da Pessoa Jurídica)
 * identifier (14 alphanumeric characters). Matches {@see CnpjFormatterOptions::CNPJ_LENGTH}.
 */
const CNPJ_LENGTH = 14;

/**
 * Helper function to simplify the usage of the {@see CnpjFormatter} class.
 *
 * Formats a CNPJ string according to the given options. With no options,
 * returns the traditional CNPJ format (e.g. `12.345.678/0009-10`). Invalid
 * input length is handled by the configured `onFail` callback instead of
 * throwing.
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
 * @throws \Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsTypeError If any option has an invalid type.
 * @throws \Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsHiddenRangeInvalidException If `hiddenStart` or
 *   `hiddenEnd` are out of valid range.
 * @throws \Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsForbiddenKeyCharacterException If any key option
 *   contains a disallowed character.
 */
function cnpj_fmt(
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
    $formatter = new CnpjFormatter(
        $options,
        $hidden,
        $hiddenKey,
        $hiddenStart,
        $hiddenEnd,
        $dotKey,
        $slashKey,
        $dashKey,
        $escape,
        $encode,
        $onFail,
    );

    return $formatter->format($cnpjInput);
}
