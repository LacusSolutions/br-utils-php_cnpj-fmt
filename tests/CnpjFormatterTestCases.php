<?php

declare(strict_types=1);

namespace Lacus\CnpjFmt\Tests;

use Closure;
use InvalidArgumentException;
use TypeError;

trait CnpjFormatterTestCases
{
    abstract protected function format(
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
    ): string;

    public function testCnpjWithDotsAndDashFormatsToSameFormat(): void
    {
        $cnpj = $this->format('03.603.568/0001-95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithoutFormattingFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03603568000195');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithDashesFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03-603-568-0001-95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithSpacesFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03 603 568 0001 95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithTrailingSpaceFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03603568000195 ');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithLeadingSpaceFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format(' 03603568000195');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithIndividualDotsFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('0.3.6.0.3.5.6.8.0.0.0.1.9.5');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithIndividualDashesFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('0-3-6-0-3-5-6-8-0-0-0-1-9-5');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithIndividualSpacesFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('0 3 6 0 3 5 6 8 0 0 0 1 9 5');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithLettersFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03603568000195abc');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithMixedCharactersFormatsCorrectly(): void
    {
        $cnpj = $this->format('036035680001 dv 95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithSlashFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03/603/568/0001/95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithSpacesAndSlashFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03 603 568 / 0001 95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithSlashAndDashMixedFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03-603-568-0001/95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithLettersAndNumbersFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('03603568slash0001dash95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjWithDvTextFormatsToDotsAndDash(): void
    {
        $cnpj = $this->format('036035680001 dv 95');

        $this->assertEquals('03.603.568/0001-95', $cnpj);
    }

    public function testCnpjFormatsToCustomDelimitersWithoutDots(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            dotKey: ''
        );

        $this->assertEquals('03603568/0001-95', $cnpj);
    }

    public function testCnpjFormatsToCustomDelimitersWithSlashAsColon(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            slashKey: ':'
        );

        $this->assertEquals('03.603.568:0001-95', $cnpj);
    }

    public function testCnpjFormatsToCustomDelimitersWithDashAsDot(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            dashKey: '.'
        );

        $this->assertEquals('03.603.568/0001.95', $cnpj);
    }

    public function testCnpjFormatsToNoDelimiters(): void
    {
        $cnpj = $this->format(
            '03.603.568/0001-95',
            dotKey: '',
            slashKey: '',
            dashKey: ''
        );

        $this->assertEquals('03603568000195', $cnpj);
    }

    public function testCnpjFormatsToCustomDelimitersWithEscape(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            escape: true,
            dotKey: '<',
            slashKey: '&',
            dashKey: '>'
        );

        $this->assertEquals('03&lt;603&lt;568&amp;0001&gt;95', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormat(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true
        );

        $this->assertEquals('03.603.***/****-**', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithStartRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenStart: 8
        );

        $this->assertEquals('03.603.568/****-**', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithEndRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenEnd: 11
        );

        $this->assertEquals('03.603.***/****-95', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithStartAndEndRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenStart: 0,
            hiddenEnd: 7
        );

        $this->assertEquals('**.***.***/0001-95', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithReversedRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenStart: 11,
            hiddenEnd: 2
        );

        $this->assertEquals('03.***.***/****-95', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithCustomKey(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenKey: '#'
        );

        $this->assertEquals('03.603.###/####-##', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithCustomKeyAndRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenKey: '#',
            hiddenStart: 8
        );

        $this->assertEquals('03.603.568/####-##', $cnpj);
    }

    public function testInvalidInputFallsBackToOnFailCallback(): void
    {
        $cnpj = $this->format(
            'abc',
            onFail: function ($value) {
                return strtoupper($value);
            }
        );

        $this->assertEquals('ABC', $cnpj);
    }

    public function testOptionWithRangeStartMinusOneThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->format(
            '03603568000195',
            hidden: true,
            hiddenStart: -1
        );
    }

    public function testOptionWithRangeStartGreaterThan13ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->format(
            '03603568000195',
            hidden: true,
            hiddenStart: 14
        );
    }

    public function testOptionWithRangeEndMinusOneThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->format(
            '03603568000195',
            hidden: true,
            hiddenEnd: -1
        );
    }

    public function testOptionWithRangeEndGreaterThan13ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->format(
            '03603568000195',
            hidden: true,
            hiddenEnd: 14
        );
    }

    public function testCnpjFormatsToHiddenFormatWithCustomKeyAndStartRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenKey: '#',
            hiddenStart: 8
        );

        $this->assertEquals('03.603.568/####-##', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithCustomKeyAndEndRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenKey: '#',
            hiddenEnd: 11
        );

        $this->assertEquals('03.603.###/####-95', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithCustomKeyAndBothRanges(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenKey: '#',
            hiddenStart: 0,
            hiddenEnd: 7
        );

        $this->assertEquals('##.###.###/0001-95', $cnpj);
    }

    public function testCnpjFormatsToHiddenFormatWithCustomKeyAndReversedRange(): void
    {
        $cnpj = $this->format(
            '03603568000195',
            hidden: true,
            hiddenKey: '#',
            hiddenStart: 11,
            hiddenEnd: 2
        );

        $this->assertEquals('03.###.###/####-95', $cnpj);
    }

    public function testOptionWithOnFailAsNotFunctionThrowsException(): void
    {
        $this->expectException(TypeError::class);

        $this->format(
            '03603568000195',
            onFail: 'testing'
        );
    }
}
