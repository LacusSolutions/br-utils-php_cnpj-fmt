<?php

declare(strict_types=1);

use Closure;
use Lacus\BrUtils\Cnpj\CnpjFormatter;
use Lacus\BrUtils\Cnpj\CnpjFormatterOptions;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputLengthException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputTypeError;

describe('CnpjFormatter', function () {
    describe('constructor', function () {
        describe('when called with no arguments', function () {
            it('creates an instance with default options', function () {
                $defaultOptions = new CnpjFormatterOptions();

                $formatter = new CnpjFormatter();

                expect($formatter->getOptions()->getAll())->toBe($defaultOptions->getAll());
            });
        });

        describe('when called with arguments', function () {
            it('uses the provided options instance', function () {
                $options = new CnpjFormatterOptions();

                $formatter = new CnpjFormatter($options);

                expect($formatter->getOptions())->toBe($options);
            });

            it('overrides the default options with the provided ones (named arguments)', function () {
                $options = [
                    'hidden' => true,
                    'slashKey' => '|',
                    'dotKey' => '_',
                    'encode' => true,
                ];

                $formatter = new CnpjFormatter(...$options);

                expect($formatter->getOptions()->getAll())->toMatchArray($options);
            });

            it('overrides the default options with the provided ones (`CnpjFormatterOptions` instance)', function () {
                $options = new CnpjFormatterOptions(
                    hidden: true,
                    slashKey: '|',
                    dotKey: '_',
                    encode: true,
                );

                $formatter = new CnpjFormatter($options);

                expect($formatter->getOptions()->getAll())->toBe($options->getAll());
            });
        });
    });

    describe('`format` method', function () {
        $format = null;

        beforeEach(function () use (&$format) {
            $formatter = new CnpjFormatter();

            $format = Closure::fromCallable([$formatter, 'format']);
        });

        describe('when input is a string with only digits', function () use (&$format) {
            it('handles the input with no formatting', function () use (&$format) {
                $result = $format('12345678000910');

                expect($result)->toBe('12.345.678/0009-10');
            });

            it('handles the input with standard formatting', function () use (&$format) {
                $result = $format('12.345.678/0009-10');

                expect($result)->toBe('12.345.678/0009-10');
            });

            it('handles the input with custom formatting', function () use (&$format) {
                $result = $format('12 345 678 | 0009 _ 10');

                expect($result)->toBe('12.345.678/0009-10');
            });
        });

        describe('when input is a string with only letters', function () use (&$format) {
            it('handles the input with no formatting', function () use (&$format) {
                $result = $format('ABCDEFGHIJKLMN');

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('handles the input with standard formatting', function () use (&$format) {
                $result = $format('AB.CDE.FGH/IJKL-MN');

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('handles the input with custom formatting', function () use (&$format) {
                $result = $format('AB CDE FGH | IJKL _ MN');

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('converts lowercase letters to uppercase', function () use (&$format) {
                $result = $format('AbCdEfGhIjKlMn');

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });
        });

        describe('when input is a string with mixed digits and letters characters', function () use (&$format) {
            it('handles the input with no formatting', function () use (&$format) {
                $result = $format('12ABC34500DE00');

                expect($result)->toBe('12.ABC.345/00DE-00');
            });

            it('handles the input with standard formatting', function () use (&$format) {
                $result = $format('12.ABC.345/00DE-00');

                expect($result)->toBe('12.ABC.345/00DE-00');
            });

            it('handles the input with custom formatting', function () use (&$format) {
                $result = $format('12 ABC 345 | 00DE _ 00');

                expect($result)->toBe('12.ABC.345/00DE-00');
            });

            it('converts lowercase letters to uppercase', function () use (&$format) {
                $result = $format('12abcDEF00eF00');

                expect($result)->toBe('12.ABC.DEF/00EF-00');
            });
        });

        describe('when input is an array', function () use (&$format) {
            it('handles array of only digits', function () use (&$format) {
                $result = $format(['1', '2', '3', '4', '5', '6', '7', '8', '0', '0', '0', '9', '1', '0', ]);

                expect($result)->toBe('12.345.678/0009-10');
            });

            it('handles array of single item with only digits', function () use (&$format) {
                $result = $format(['12345678000910']);

                expect($result)->toBe('12.345.678/0009-10');
            });

            it('handles array of grouped digits', function () use (&$format) {
                $result = $format(['12', '345', '678', '0009', '10']);

                expect($result)->toBe('12.345.678/0009-10');
            });

            it('handles array of grouped digits and punctuation', function () use (&$format) {
                $result = $format(['12', '.', '345', '.', '678', '/', '0009', '-', '10']);

                expect($result)->toBe('12.345.678/0009-10');
            });

            it('handles array of only letters', function () use (&$format) {
                $result = $format(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N']);

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('handles array of single item with only letters', function () use (&$format) {
                $result = $format(['ABCDEFGHIJKLMN']);

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('handles array of lowercase letters', function () use (&$format) {
                $result = $format(['abcdefghijklmn']);

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('handles array of grouped letters', function () use (&$format) {
                $result = $format(['AB', 'CDE', 'FGH', 'IJKL', 'MN']);

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('handles array of grouped letters and punctuation', function () use (&$format) {
                $result = $format(['AB', '.', 'CDE', '.', 'FGH', '/', 'IJKL', '-', 'MN']);

                expect($result)->toBe('AB.CDE.FGH/IJKL-MN');
            });

            it('handles array of mixed digits and letters', function () use (&$format) {
                $result = $format(['1', '2', 'a', 'b', 'c', 'D', 'E', 'F', '0', '0', 'g', 'H', '3', '4']);

                expect($result)->toBe('12.ABC.DEF/00GH-34');
            });

            it('handles array of single item with mixed digits and letters', function () use (&$format) {
                $result = $format(['12abcDEF00gH34']);

                expect($result)->toBe('12.ABC.DEF/00GH-34');
            });

            it('handles array of grouped digits and letters', function () use (&$format) {
                $result = $format(['12', 'abc', 'DEF', '00gH', '34']);

                expect($result)->toBe('12.ABC.DEF/00GH-34');
            });

            it('handles array of grouped digits, letters and punctuation', function () use (&$format) {
                $result = $format(['12', '.', 'abc', '.', 'DEF', '/', '00gH', '-', '34']);

                expect($result)->toBe('12.ABC.DEF/00GH-34');
            });
        });

        describe('when input is not string or array of strings', function () use (&$format) {
            it('throws CnpjFormatterInputTypeError on input of null', function () use (&$format) {
                try {
                    $format(null);
                    expect(false)->toBeTrue('expected exception');
                } catch (CnpjFormatterInputTypeError $error) {
                    expect($error)->toBeInstanceOf(CnpjFormatterInputTypeError::class);
                    expect($error->expectedType)->toBe('string or string[]');
                    expect($error->actualInput)->toBeNull();
                    expect($error->actualType)->toBe('null');
                }
            });

            it('throws CnpjFormatterInputTypeError on integer number', function () use (&$format) {
                try {
                    $format(42);

                    expect(false)->toBeTrue('expected exception');
                } catch (CnpjFormatterInputTypeError $error) {
                    expect($error->expectedType)->toBe('string or string[]');
                    expect($error->actualInput)->toBe(42);
                    expect($error->actualType)->toBe('integer number');
                }
            });

            it('throws CnpjFormatterInputTypeError on float number', function () use (&$format) {
                try {
                    $format(3.14);

                    expect(false)->toBeTrue('expected exception');
                } catch (CnpjFormatterInputTypeError $error) {
                    expect($error->expectedType)->toBe('string or string[]');
                    expect($error->actualInput)->toBe(3.14);
                    expect($error->actualType)->toBe('float number');
                }
            });

            it('throws CnpjFormatterInputTypeError on boolean false', function () use (&$format) {
                try {
                    $format(false);

                    expect(false)->toBeTrue('expected exception');
                } catch (CnpjFormatterInputTypeError $error) {
                    expect($error->expectedType)->toBe('string or string[]');
                    expect($error->actualInput)->toBeFalse();
                    expect($error->actualType)->toBe('boolean');
                }
            });

            it('throws CnpjFormatterInputTypeError on boolean true', function () use (&$format) {
                try {
                    $format(true);

                    expect(false)->toBeTrue('expected exception');
                } catch (CnpjFormatterInputTypeError $error) {
                    expect($error->expectedType)->toBe('string or string[]');
                    expect($error->actualInput)->toBeTrue();
                    expect($error->actualType)->toBe('boolean');
                }
            });

            it('throws CnpjFormatterInputTypeError on object', function () use (&$format) {
                $input = (object) [];

                try {
                    $format($input);

                    expect(false)->toBeTrue('expected exception');
                } catch (CnpjFormatterInputTypeError $error) {
                    expect($error->expectedType)->toBe('string or string[]');
                    expect($error->actualInput)->toBe($input);
                    expect($error->actualType)->toBe('object');
                }
            });

            it('throws CnpjFormatterInputTypeError for arrays containing non-strings', function () use (&$format) {
                $input = ['12', 34, '56'];

                try {
                    $format($input);

                    expect(false)->toBeTrue('expected exception');
                } catch (CnpjFormatterInputTypeError $error) {
                    expect($error->expectedType)->toBe('string or string[]');
                    expect($error->actualInput)->toBe($input);
                }
            });
        });

        describe('when sanitized input length is not 14', function () use (&$format) {
            $makeOnFail = static function (int $evaluatedLength): \Closure {
                return static function (mixed $value, CnpjFormatterInputLengthException $error) use ($evaluatedLength): string {
                    expect($error)->toBeInstanceOf(CnpjFormatterInputLengthException::class);
                    expect(mb_strlen($error->evaluatedInput, 'UTF-8'))->toBe($evaluatedLength);
                    expect($error->actualInput)->toBe($value);

                    return 'ERROR: "' . (is_string($value) ? $value : json_encode($value)) . '"';
                };
            };

            it('fails with CnpjFormatterInputLengthException on short inputs', function () use (&$format, $makeOnFail) {
                $cases = [
                    ['1', 1],
                    ['12', 2],
                    ['12.A', 3],
                    ['12.AB', 4],
                    ['12.ABC', 5],
                    ['12.ABC.3', 6],
                    ['12.ABC.34', 7],
                    ['12.ABC.345', 8],
                    ['12.ABC.345/0', 9],
                    ['12.ABC.345/00', 10],
                    ['12.ABC.345/00D', 11],
                    ['12.ABC.345/00DE', 12],
                    ['12.ABC.345/00DE-6', 13],
                    ['12.ABC.345/00DE-678', 15],
                ];

                foreach ($cases as [$input, $length]) {
                    $format($input, onFail: $makeOnFail($length));
                }
            });
        });

        describe('when using `hidden` option', function () use (&$format) {
            $defaultHiddenLength = CnpjFormatterOptions::DEFAULT_HIDDEN_END - CnpjFormatterOptions::DEFAULT_HIDDEN_START + 1;
            $standardCnpjFormatLength = strlen('00.000.000/0000-00');

            it('replaces some characters with "*" when simply `true`', function () use (&$format, $defaultHiddenLength, $standardCnpjFormatLength) {
                $result = $format('12ABC34500DE99', hidden: true);
                $hiddenCount = substr_count($result, '*');

                expect($hiddenCount)->toBe($defaultHiddenLength);
                expect(mb_strlen($result, 'UTF-8'))->toBe($standardCnpjFormatLength);
            });

            it('replaces characters with "*" in a given range', function () use (&$format, $standardCnpjFormatLength) {
                $result = $format('12ABC34500DE99', hidden: true, hiddenStart: 8, hiddenEnd: 11);

                expect($result)->toBe('12.ABC.345/****-99');
                expect(mb_strlen($result, 'UTF-8'))->toBe($standardCnpjFormatLength);
            });

            it('replaces characters with a custom key', function () use (&$format, $defaultHiddenLength, $standardCnpjFormatLength) {
                $result = $format('12ABC34500DE99', hidden: true, hiddenKey: '#');
                $hiddenCount = substr_count($result, '#');

                expect($result)->not->toContain('*');
                expect($hiddenCount)->toBe($defaultHiddenLength);
                expect(mb_strlen($result, 'UTF-8'))->toBe($standardCnpjFormatLength);
            });

            it('replaces characters with a custom zero-width key', function () use (&$format, $defaultHiddenLength, $standardCnpjFormatLength) {
                $result = $format('12ABC34500DE99', hidden: true, hiddenKey: '');

                expect($result)->not->toContain('*');
                expect(mb_strlen($result, 'UTF-8'))->toBe($standardCnpjFormatLength - $defaultHiddenLength);
            });

            it('replaces characters with a custom multi-character key', function () use (&$format, $defaultHiddenLength, $standardCnpjFormatLength) {
                $result = $format('12ABC34500DE99', hidden: true, hiddenKey: '[]');
                preg_match_all('/\\[\\]/', $result, $matches);
                $bracketPairs = strlen(implode('', $matches[0])) / 2;

                expect($result)->not->toContain('*');
                expect($bracketPairs)->toBe($defaultHiddenLength);
                expect(mb_strlen($result, 'UTF-8'))->toBe($standardCnpjFormatLength + $defaultHiddenLength);
            });
        });

        describe('when customizing punctuation', function () use (&$format) {
            it('replaces dots with a custom key', function () use (&$format) {
                $result = $format('12ABC34500DE99', dotKey: ' ');

                expect($result)->toBe('12 ABC 345/00DE-99');
            });

            it('replaces dots with a custom zero-width key', function () use (&$format) {
                expect($format('12ABC34500DE99', ['dotKey' => '']))->toBe('12ABC345/00DE-99');
            });

            it('replaces dots with a custom multi-character key', function () use (&$format) {
                $result = $format('12ABC34500DE99', dotKey: '[]');

                expect($result)->toBe('12[]ABC[]345/00DE-99');
            });

            it('replaces slash with a custom key', function () use (&$format) {
                $result = $format('12ABC34500DE99', slashKey: '|');

                expect($result)->toBe('12.ABC.345|00DE-99');
            });

            it('replaces slash with a custom zero-width key', function () use (&$format) {
                $result = $format('12ABC34500DE99', slashKey: '');

                expect($result)->toBe('12.ABC.34500DE-99');
            });

            it('replaces slash with a custom multi-character key', function () use (&$format) {
                $result = $format('12ABC34500DE99', slashKey: '[]');

                expect($result)->toBe('12.ABC.345[]00DE-99');
            });

            it('replaces dash with a custom key', function () use (&$format) {
                $result = $format('12ABC34500DE99', dashKey: '_');

                expect($result)->toBe('12.ABC.345/00DE_99');
            });

            it('replaces dash with a custom zero-width key', function () use (&$format) {
                $result = $format('12ABC34500DE99', dashKey: '');

                expect($result)->toBe('12.ABC.345/00DE99');
            });

            it('replaces dash with a custom multi-character key', function () use (&$format) {
                $result = $format('12ABC34500DE99', dashKey: '[]');

                expect($result)->toBe('12.ABC.345/00DE[]99');
            });
        });

        describe('when using `escape` option ', function () use (&$format) {
            it('escapes HTML special characters', function () use (&$format) {
                $result = $format('12ABC34500DE99', dotKey: '&', slashKey: '"', dashKey: '<>', escape: true);

                expect($result)->toBe('12&amp;ABC&amp;345&quot;00DE&lt;&gt;99');
            });
        });

        describe('when using `encode` option ', function () use (&$format) {
            it('URL-encodes the result', function () use (&$format) {
                $result = $format('12ABC34500DE99', encode: true);

                expect($result)->toBe('12.ABC.345%2F00DE-99');
            });
        });

        describe('edge cases', function () use (&$format) {
            it('replaces `hiddenKey`, `dotKey`, `slashKey` and `dashKey` use multi-characters value', function () use (&$format) {
                $result = $format(
                    '12ABC34500DE99',
                    hidden: true,
                    hiddenStart: 5,
                    hiddenEnd: 9,
                    hiddenKey: '[*]',
                    dotKey: '[.]',
                    slashKey: '[/]',
                    dashKey: '[-]',
                );

                expect($result)->toBe('12[.]ABC[.][*][*][*][/][*][*]DE[-]99');
            });
        });
    });
});
