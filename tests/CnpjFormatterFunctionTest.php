<?php

declare(strict_types=1);

namespace Lacus\CnpjFmt\Tests;

use Closure;

use function Lacus\CnpjFmt\cnpj_fmt;

use PHPUnit\Framework\TestCase;

class CnpjFormatterFunctionTest extends TestCase
{
    use CnpjFormatterTestCases;

    protected function format(
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
        return cnpj_fmt(
            $cnpjString,
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
    }
}
