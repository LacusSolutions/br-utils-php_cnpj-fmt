<?php

declare(strict_types=1);

namespace Lacus\CnpjFmt;

use Closure;

const CNPJ_LENGTH = 14;

function cnpj_fmt(
    string $cnpjString,
    ?bool $escape = null,
    ?bool $hidden = null,
    ?string $hiddenKey = null,
    ?int $hiddenStart = null,
    ?int $hiddenEnd = null,
    ?string $dotKey = null,
    ?string $slashKey = null,
    ?string $dashKey = null,
    ?Closure $onFail = null,
): string {
    $formatter = new CnpjFormatter(
        $escape,
        $hidden,
        $hiddenKey,
        $hiddenStart,
        $hiddenEnd,
        $dotKey,
        $slashKey,
        $dashKey,
        $onFail,
    );

    return $formatter->format($cnpjString);
}
