<?php

declare(strict_types=1);

namespace Lacus\CnpjFmt\Tests;

use Closure;
use Lacus\CnpjFmt\CnpjFormatter;
use Lacus\CnpjFmt\CnpjFormatterOptions;
use PHPUnit\Framework\TestCase;

class CnpjFormatterClassTest extends TestCase
{
    use CnpjFormatterTestCases;

    private CnpjFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new CnpjFormatter();
    }

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
        return $this->formatter->format(
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

    public function testObjectOrientedGetOptions(): void
    {
        $options = $this->formatter->getOptions();

        $this->assertInstanceOf(CnpjFormatterOptions::class, $options);
        $this->assertFalse($options->isEscaped());
        $this->assertFalse($options->isHidden());
        $this->assertEquals('*', $options->getHiddenKey());
        $this->assertEquals(5, $options->getHiddenStart());
        $this->assertEquals(13, $options->getHiddenEnd());
        $this->assertEquals('.', $options->getDotKey());
        $this->assertEquals('/', $options->getSlashKey());
        $this->assertEquals('-', $options->getDashKey());
    }
}
