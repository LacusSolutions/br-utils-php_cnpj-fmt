<?php

declare(strict_types=1);

use Lacus\BrUtils\Cnpj\CnpjFormatterOptions;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputLengthException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsForbiddenKeyCharacterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsHiddenRangeInvalidException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsTypeError;
use ReflectionMethod;

describe('CnpjFormatterOptions', function () {
    $defaultParameters = [
        'hidden' => CnpjFormatterOptions::DEFAULT_HIDDEN,
        'hiddenKey' => CnpjFormatterOptions::DEFAULT_HIDDEN_KEY,
        'hiddenStart' => CnpjFormatterOptions::DEFAULT_HIDDEN_START,
        'hiddenEnd' => CnpjFormatterOptions::DEFAULT_HIDDEN_END,
        'dotKey' => CnpjFormatterOptions::DEFAULT_DOT_KEY,
        'slashKey' => CnpjFormatterOptions::DEFAULT_SLASH_KEY,
        'dashKey' => CnpjFormatterOptions::DEFAULT_DASH_KEY,
        'escape' => CnpjFormatterOptions::DEFAULT_ESCAPE,
        'encode' => CnpjFormatterOptions::DEFAULT_ENCODE,
        'onFail' => CnpjFormatterOptions::getDefaultOnFail(),
    ];

    describe('constructor', function () use ($defaultParameters) {
        describe('when called with no parameters', function () use ($defaultParameters) {
            it('sets all options to default values', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions();

                expect($options->getAll())->toBe($defaultParameters);
            });
        });

        describe('when called with all parameters with null values', function () use ($defaultParameters) {
            it('sets all options to default values', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(
                    hidden: null,
                    hiddenKey: null,
                    hiddenStart: null,
                    hiddenEnd: null,
                    dotKey: null,
                    slashKey: null,
                    dashKey: null,
                    escape: null,
                    encode: null,
                    onFail: null,
                );

                expect($options->getAll())->toBe($defaultParameters);
            });
        });

        describe('when called with all parameters', function () {
            it('sets all options to the provided values', function () {
                $parameters = [
                    'hidden' => true,
                    'hiddenKey' => '#',
                    'hiddenStart' => 1,
                    'hiddenEnd' => 8,
                    'dotKey' => '|',
                    'slashKey' => '_',
                    'dashKey' => '~',
                    'escape' => true,
                    'encode' => true,
                    'onFail' => function (mixed $value): string {
                        return "ERROR: {$value}";
                    },
                ];

                $options = new CnpjFormatterOptions(...$parameters);

                expect($options->getAll())->toBe($parameters);
            });
        });

        describe('when called with some parameters', function () use ($defaultParameters) {
            it('sets only the provided non-nullish values', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(
                    hidden: true,
                    hiddenKey: '#',
                    hiddenStart: null,
                    hiddenEnd: null,
                    escape: true,
                    encode: false,
                    onFail: null,
                );

                expect($options->getAll())->toBe([
                    ...$defaultParameters,
                    'hidden' => true,
                    'hiddenKey' => '#',
                    'escape' => true,
                    'encode' => false,
                ]);
            });
        });

        describe('when called with overrides parameters', function () {
            it('uses last param option with 2 params', function () {
                $options = new CnpjFormatterOptions(
                    overrides: [
                        ['hiddenKey' => '#'],
                        ['hiddenKey' => 'X'],
                    ],
                );

                expect($options->hiddenKey)->toBe('X');
            });

            it('uses last param option with 1 array and 1 object instance', function () {
                $options = new CnpjFormatterOptions(
                    overrides: [
                        ['hiddenKey' => '#'],
                        new CnpjFormatterOptions(hiddenKey: 'X'),
                    ],
                );

                expect($options->hiddenKey)->toBe('X');
            });

            it('uses last param option with 5 params', function () {
                $options = new CnpjFormatterOptions(
                    overrides: [
                        ['hiddenKey' => '.'],
                        new CnpjFormatterOptions(hiddenKey: '_'),
                        ['hiddenKey' => '#'],
                        new CnpjFormatterOptions(hiddenKey: 'X'),
                        ['hiddenKey' => '@'],
                    ],
                );

                expect($options->hiddenKey)->toBe('@');
            });
        });
    });

    describe('`hidden` property', function () use ($defaultParameters) {
        describe('when setting to a boolean value', function () {
            it('sets `hidden` to `true`', function () {
                $options = new CnpjFormatterOptions(hidden: false);

                $options->hidden = true;

                expect($options->hidden)->toBeTrue();
            });

            it('sets `hidden` to `false`', function () {
                $options = new CnpjFormatterOptions(hidden: true);

                $options->hidden = false;

                expect($options->hidden)->toBeFalse();
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(hidden: !CnpjFormatterOptions::DEFAULT_HIDDEN);

                $options->hidden = null;

                expect($options->hidden)->toBe($defaultParameters['hidden']);
            });
        });

        describe('when setting to a non-boolean value', function () {
            it('coerces object value to `true`', function () {
                $options = new CnpjFormatterOptions(hidden: false);

                $options->hidden = (object) ['not' => 'a boolean'];

                expect($options->hidden)->toBeTrue();
            });

            it('coerces truthy string value to `true`', function () {
                $options = new CnpjFormatterOptions(hidden: false);

                $options->hidden = 'not a boolean';

                expect($options->hidden)->toBeTrue();
            });

            it('coerces truthy number value to `true`', function () {
                $options = new CnpjFormatterOptions(hidden: false);

                $options->hidden = 123;

                expect($options->hidden)->toBeTrue();
            });

            it('coerces empty string value to `false`', function () {
                $options = new CnpjFormatterOptions(hidden: false);

                $options->hidden = '';

                expect($options->hidden)->toBeFalse();
            });

            it('coerces zero number value to `false`', function () {
                $options = new CnpjFormatterOptions(hidden: false);

                $options->hidden = 0;

                expect($options->hidden)->toBeFalse();
            });
        });
    });

    describe('`hiddenKey` property', function () use ($defaultParameters) {
        describe('when setting to a string value', function () {
            it('sets `hiddenKey` to the provided value', function () {
                $options = new CnpjFormatterOptions(hiddenKey: '*');

                $options->hiddenKey = 'X';

                expect($options->hiddenKey)->toBe('X');
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(hiddenKey: '#');

                $options->hiddenKey = null;

                expect($options->hiddenKey)->toBe($defaultParameters['hiddenKey']);
            });
        });

        describe('when setting to a non-string value', function () {
            it('throws CnpjFormatterOptionsTypeError with an object', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenKey = (object) ['not' => 'a string'];
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenKey" must be of type string. Got object.');
            });

            it('throws CnpjFormatterOptionsTypeError with a number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenKey = 123;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenKey" must be of type string. Got integer number.');
            });

            it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenKey = true;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenKey" must be of type string. Got boolean.');
            });
        });

        describe('when setting to a string containing a forbidden character', function () {
            it("throws CnpjFormatterOptionsForbiddenKeyCharacterException with %s", function (string $forbiddenChar) {
                $options = new CnpjFormatterOptions();
                $forbiddenCharsQuoted = '"' . implode('", "', CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS) . '"';

                $msg = 'Value "' . $forbiddenChar . '" for CNPJ formatting option "hiddenKey" contains disallowed characters (' . $forbiddenCharsQuoted . ').';

                expect(function () use ($options, $forbiddenChar) {
                    $options->hiddenKey = $forbiddenChar;
                })->toThrow(CnpjFormatterOptionsForbiddenKeyCharacterException::class, $msg);
            })->with(CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS);
        });
    });

    describe('`hiddenStart` property', function () use ($defaultParameters) {
        describe('when setting to a number value', function () {
            it('sets `hiddenStart` to the provided value', function () {
                $options = new CnpjFormatterOptions(hiddenStart: 0);

                $options->hiddenStart = 1;

                expect($options->hiddenStart)->toBe(1);
            });
        });

        describe('when setting to an invalid number value range', function () {
            it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a negative number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenStart = -1;
                })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenStart" must be an integer between 0 and 13. Got -1.');
            });

            it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a number greater than 13', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenStart = 14;
                })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenStart" must be an integer between 0 and 13. Got 14.');
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(hiddenStart: 0);

                $options->hiddenStart = null;

                expect($options->hiddenStart)->toBe($defaultParameters['hiddenStart']);
            });
        });

        describe('when setting to a non-integer value', function () {
            it('throws CnpjFormatterOptionsTypeError with an object', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenStart = (object) ['not' => 'a number'];
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got object.');
            });

            it('throws CnpjFormatterOptionsTypeError with a string', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenStart = 'not a number';
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got string.');
            });

            it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenStart = true;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got boolean.');
            });

            it('throws CnpjFormatterOptionsTypeError with a float number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenStart = 1.5;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got float number.');
            });
        });
    });

    describe('`hiddenEnd` property', function () use ($defaultParameters) {
        describe('when setting to a number value', function () {
            it('sets `hiddenEnd` to the provided value', function () {
                $options = new CnpjFormatterOptions(hiddenEnd: 13);

                $options->hiddenEnd = 12;

                expect($options->hiddenEnd)->toBe(12);
            });
        });

        describe('when setting to an invalid number value range', function () {
            it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a negative number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenEnd = -1;
                })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenEnd" must be an integer between 0 and 13. Got -1.');
            });

            it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a number greater than 13', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenEnd = 14;
                })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenEnd" must be an integer between 0 and 13. Got 14.');
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(hiddenEnd: 0);

                $options->hiddenEnd = null;

                expect($options->hiddenEnd)->toBe($defaultParameters['hiddenEnd']);
            });
        });

        describe('when setting to a non-integer value', function () {
            it('throws CnpjFormatterOptionsTypeError with an object', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenEnd = (object) ['not' => 'a number'];
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got object.');
            });

            it('throws CnpjFormatterOptionsTypeError with a string', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenEnd = 'not a number';
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got string.');
            });

            it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenEnd = true;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got boolean.');
            });

            it('throws CnpjFormatterOptionsTypeError with a float number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->hiddenEnd = 1.5;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got float number.');
            });
        });
    });

    describe('`dotKey` property', function () use ($defaultParameters) {
        describe('when setting to a string value', function () {
            it('sets `dotKey` to the provided value', function () {
                $options = new CnpjFormatterOptions(dotKey: '.');

                $options->dotKey = '_';

                expect($options->dotKey)->toBe('_');
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(dotKey: '_');

                $options->dotKey = null;

                expect($options->dotKey)->toBe($defaultParameters['dotKey']);
            });
        });

        describe('when setting to a non-string value', function () {
            it('throws CnpjFormatterOptionsTypeError with an object', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->dotKey = (object) ['not' => 'a string'];
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "dotKey" must be of type string. Got object.');
            });

            it('throws CnpjFormatterOptionsTypeError with a number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->dotKey = 123;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "dotKey" must be of type string. Got integer number.');
            });

            it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->dotKey = true;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "dotKey" must be of type string. Got boolean.');
            });
        });

        describe('when setting to a string containing a forbidden key character', function () {
            it("throws CnpjFormatterOptionsForbiddenKeyCharacterException with %s", function (string $forbiddenChar) {
                $options = new CnpjFormatterOptions();
                $forbiddenCharsQuoted = '"' . implode('", "', CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS) . '"';

                $msg = 'Value "' . $forbiddenChar . '" for CNPJ formatting option "dotKey" contains disallowed characters (' . $forbiddenCharsQuoted . ').';

                expect(function () use ($options, $forbiddenChar) {
                    $options->dotKey = $forbiddenChar;
                })->toThrow(CnpjFormatterOptionsForbiddenKeyCharacterException::class, $msg);
            })->with(CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS);
        });
    });

    describe('`slashKey` property', function () use ($defaultParameters) {
        describe('when setting to a string value', function () {
            it('sets `slashKey` to the provided value', function () {
                $options = new CnpjFormatterOptions(slashKey: '.');

                $options->slashKey = '_';

                expect($options->slashKey)->toBe('_');
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(slashKey: '_');

                $options->slashKey = null;

                expect($options->slashKey)->toBe($defaultParameters['slashKey']);
            });
        });

        describe('when setting to a non-string value', function () {
            it('throws CnpjFormatterOptionsTypeError with an object', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->slashKey = (object) ['not' => 'a string'];
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "slashKey" must be of type string. Got object.');
            });

            it('throws CnpjFormatterOptionsTypeError with a number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->slashKey = 123;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "slashKey" must be of type string. Got integer number.');
            });

            it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->slashKey = true;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "slashKey" must be of type string. Got boolean.');
            });
        });

        describe('when setting to a string containing a forbidden key character', function () {
            it("throws CnpjFormatterOptionsForbiddenKeyCharacterException with %s", function (string $forbiddenChar) {
                $options = new CnpjFormatterOptions();
                $forbiddenCharsQuoted = '"' . implode('", "', CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS) . '"';

                $msg = 'Value "' . $forbiddenChar . '" for CNPJ formatting option "slashKey" contains disallowed characters (' . $forbiddenCharsQuoted . ').';

                expect(function () use ($options, $forbiddenChar) {
                    $options->slashKey = $forbiddenChar;
                })->toThrow(CnpjFormatterOptionsForbiddenKeyCharacterException::class, $msg);
            })->with(CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS);
        });
    });

    describe('`dashKey` property', function () use ($defaultParameters) {
        describe('when setting to a string value', function () {
            it('sets `dashKey` to the provided value', function () {
                $options = new CnpjFormatterOptions(dashKey: '.');

                $options->dashKey = '_';

                expect($options->dashKey)->toBe('_');
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(dashKey: '_');

                $options->dashKey = null;

                expect($options->dashKey)->toBe($defaultParameters['dashKey']);
            });
        });

        describe('when setting to a non-string value', function () {
            it('throws CnpjFormatterOptionsTypeError with an object', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->dashKey = (object) ['not' => 'a string'];
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "dashKey" must be of type string. Got object.');
            });

            it('throws CnpjFormatterOptionsTypeError with a number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->dashKey = 123;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "dashKey" must be of type string. Got integer number.');
            });

            it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->dashKey = true;
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "dashKey" must be of type string. Got boolean.');
            });
        });

        describe('when setting to a string containing a forbidden key character', function () {
            it("throws CnpjFormatterOptionsForbiddenKeyCharacterException with %s", function (string $forbiddenChar) {
                $options = new CnpjFormatterOptions();
                $forbiddenCharsQuoted = '"' . implode('", "', CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS) . '"';

                $msg = 'Value "' . $forbiddenChar . '" for CNPJ formatting option "dashKey" contains disallowed characters (' . $forbiddenCharsQuoted . ').';

                expect(function () use ($options, $forbiddenChar) {
                    $options->dashKey = $forbiddenChar;
                })->toThrow(CnpjFormatterOptionsForbiddenKeyCharacterException::class, $msg);
            })->with(CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS);
        });
    });

    describe('`escape` property', function () use ($defaultParameters) {
        describe('when setting to a boolean value', function () {
            it('sets `escape` to `true`', function () {
                $options = new CnpjFormatterOptions(escape: false);

                $options->escape = true;

                expect($options->escape)->toBeTrue();
            });

            it('sets `escape` to `false`', function () {
                $options = new CnpjFormatterOptions(escape: true);

                $options->escape = false;

                expect($options->escape)->toBeFalse();
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(escape: !CnpjFormatterOptions::DEFAULT_ESCAPE);

                $options->escape = null;

                expect($options->escape)->toBe($defaultParameters['escape']);
            });
        });

        describe('when setting to a non-boolean value', function () {
            it('coerces object value to `true`', function () {
                $options = new CnpjFormatterOptions(escape: false);

                $options->escape = (object) ['not' => 'a boolean'];

                expect($options->escape)->toBeTrue();
            });

            it('coerces truthy string value to `true`', function () {
                $options = new CnpjFormatterOptions(escape: false);

                $options->escape = 'not a boolean';

                expect($options->escape)->toBeTrue();
            });

            it('coerces truthy number value to `true`', function () {
                $options = new CnpjFormatterOptions(escape: false);

                $options->escape = 123;

                expect($options->escape)->toBeTrue();
            });

            it('coerces empty string value to `false`', function () {
                $options = new CnpjFormatterOptions(escape: false);

                $options->escape = '';

                expect($options->escape)->toBeFalse();
            });

            it('coerces zero number value to `false`', function () {
                $options = new CnpjFormatterOptions(escape: false);

                $options->escape = 0;

                expect($options->escape)->toBeFalse();
            });
        });
    });

    describe('`encode` property', function () use ($defaultParameters) {
        describe('when setting to a boolean value', function () {
            it('sets `encode` to `true`', function () {
                $options = new CnpjFormatterOptions(encode: false);

                $options->encode = true;

                expect($options->encode)->toBeTrue();
            });

            it('sets `encode` to `false`', function () {
                $options = new CnpjFormatterOptions(encode: true);

                $options->encode = false;

                expect($options->encode)->toBeFalse();
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default value for `null`', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions(encode: !CnpjFormatterOptions::DEFAULT_ENCODE);

                $options->encode = null;

                expect($options->encode)->toBe($defaultParameters['encode']);
            });
        });

        describe('when setting to a non-boolean value', function () {
            it('coerces object value to `true`', function () {
                $options = new CnpjFormatterOptions(encode: false);

                $options->encode = (object) ['not' => 'a boolean'];

                expect($options->encode)->toBeTrue();
            });

            it('coerces truthy string value to `true`', function () {
                $options = new CnpjFormatterOptions(encode: false);

                $options->encode = 'not a boolean';

                expect($options->encode)->toBeTrue();
            });

            it('coerces truthy number value to `true`', function () {
                $options = new CnpjFormatterOptions(encode: false);

                $options->encode = 123;

                expect($options->encode)->toBeTrue();
            });

            it('coerces empty string value to `false`', function () {
                $options = new CnpjFormatterOptions(encode: false);

                $options->encode = '';

                expect($options->encode)->toBeFalse();
            });

            it('coerces zero number value to `false`', function () {
                $options = new CnpjFormatterOptions(encode: false);

                $options->encode = 0;

                expect($options->encode)->toBeFalse();
            });
        });
    });

    describe('`onFail` property', function () use ($defaultParameters) {
        describe('when using the default callback value', function () {
            it('returns empty string', function () {
                $exception = new CnpjFormatterInputLengthException('abc', 'abc', 14);

                $result = (CnpjFormatterOptions::getDefaultOnFail())('some value', $exception);

                expect($result)->toBe('');
            });
        });

        describe('when setting to a callable value', function () {
            it('sets `onFail` to the provided callback', function () {
                $callback = function (mixed $value, CnpjFormatterException $e): string {
                    return "ERROR: {$value}";
                };
                $options = new CnpjFormatterOptions();

                $options->onFail = $callback;

                expect($options->onFail)->toBe($callback);
            });
        });

        describe('when setting to a nullish value', function () use ($defaultParameters) {
            it('sets default callback for `null`', function () use ($defaultParameters) {
                $callback = function (mixed $value, CnpjFormatterException $e): string {
                    return "ERROR: {$value}";
                };
                $options = new CnpjFormatterOptions(onFail: $callback);

                $options->__set('onFail', null);

                expect($options->onFail)->toBe($defaultParameters['onFail']);
            });
        });

        describe('when setting to a non-callable value', function () {
            it('throws CnpjFormatterOptionsTypeError with an object', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->onFail = (object) ['not' => 'a function'];
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "onFail" must be of type function. Got object.');
            });

            it('throws CnpjFormatterOptionsTypeError with a string', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->onFail = 'not a function';
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "onFail" must be of type function. Got string.');
            });

            it('throws CnpjFormatterOptionsTypeError with a number', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->__set('onFail', 123);
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "onFail" must be of type function. Got integer number.');
            });

            it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                $options = new CnpjFormatterOptions();

                expect(function () use ($options) {
                    $options->__set('onFail', true);
                })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "onFail" must be of type function. Got boolean.');
            });
        });
    });

    describe('`getAll` method', function () {
        it('returns the all properties with expected types', function () {
            $all = (new CnpjFormatterOptions())->getAll();

            expect($all['hidden'])->toBeBool();
            expect($all['hiddenKey'])->toBeString();
            expect($all['hiddenStart'])->toBeInt();
            expect($all['hiddenEnd'])->toBeInt();
            expect($all['dotKey'])->toBeString();
            expect($all['slashKey'])->toBeString();
            expect($all['dashKey'])->toBeString();
            expect($all['escape'])->toBeBool();
            expect($all['encode'])->toBeBool();
            expect($all['onFail'])->toBeInstanceOf(\Closure::class);
        });
    });

    describe('`setHiddenRange` method', function () use ($defaultParameters) {
        describe('when called with valid values', function () {
            it('sets `hiddenStart` and `hiddenEnd` to the provided values', function () {
                $options = new CnpjFormatterOptions();

                $options->setHiddenRange(0, 10);

                expect($options->hiddenStart)->toBe(0);
                expect($options->hiddenEnd)->toBe(10);
            });

            describe('and `hiddenStart` is equal to `hiddenEnd`', function () {
                it('sets `hiddenStart` and `hiddenEnd` with 0 accordingly', function () {
                    $options = new CnpjFormatterOptions();

                    $options->setHiddenRange(0, 0);

                    expect($options->hiddenStart)->toBe(0);
                    expect($options->hiddenEnd)->toBe(0);
                });

                it('sets `hiddenStart` and `hiddenEnd` with 13 accordingly', function () {
                    $options = new CnpjFormatterOptions();

                    $options->setHiddenRange(13, 13);

                    expect($options->hiddenStart)->toBe(13);
                    expect($options->hiddenEnd)->toBe(13);
                });
            });

            describe('and `hiddenStart` is greater than `hiddenEnd`', function () {
                it('automatically swaps start and end values', function () {
                    $options = new CnpjFormatterOptions();

                    $options->setHiddenRange(8, 2);

                    expect($options->hiddenStart)->toBe(2);
                    expect($options->hiddenEnd)->toBe(8);
                });
            });
        });

        describe('when called with nullish values', function () use ($defaultParameters) {
            it('sets default values for `null` in both fields', function () use ($defaultParameters) {
                $options = new CnpjFormatterOptions();

                $options->setHiddenRange(null, null);

                expect($options->hiddenStart)->toBe($defaultParameters['hiddenStart']);
                expect($options->hiddenEnd)->toBe($defaultParameters['hiddenEnd']);
            });

            describe('when setting `hiddenStart` to a nullish value', function () use ($defaultParameters) {
                it('sets default value for `null`', function () use ($defaultParameters) {
                    $options = new CnpjFormatterOptions(hiddenStart: 0);

                    $options->setHiddenRange(null, 13);

                    expect($options->hiddenStart)->toBe($defaultParameters['hiddenStart']);
                    expect($options->hiddenEnd)->toBe(13);
                });
            });

            describe('when setting `hiddenEnd` to a nullish value', function () use ($defaultParameters) {
                it('sets default value for `null`', function () use ($defaultParameters) {
                    $options = new CnpjFormatterOptions(hiddenEnd: 13);

                    $options->setHiddenRange(0, null);

                    expect($options->hiddenStart)->toBe(0);
                    expect($options->hiddenEnd)->toBe($defaultParameters['hiddenEnd']);
                });
            });
        });

        describe('when called with invalid values', function () {
            describe('when setting `hiddenStart` to an invalid number value range', function () {
                it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a negative number', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        $options->setHiddenRange(-1, 13);
                    })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenStart" must be an integer between 0 and 13. Got -1.');
                });

                it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a number greater than 13', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        $options->setHiddenRange(14, 13);
                    })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenStart" must be an integer between 0 and 13. Got 14.');
                });
            });

            describe('when setting `hiddenEnd` to an invalid number value range', function () {
                it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a negative number', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        $options->setHiddenRange(0, -1);
                    })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenEnd" must be an integer between 0 and 13. Got -1.');
                });

                it('throws CnpjFormatterOptionsHiddenRangeInvalidException with a number greater than 13', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        $options->setHiddenRange(0, 14);
                    })->toThrow(CnpjFormatterOptionsHiddenRangeInvalidException::class, 'CNPJ formatting option "hiddenEnd" must be an integer between 0 and 13. Got 14.');
                });
            });

            describe('when setting `hiddenStart` to a non-integer value', function () {
                /**
                 * Invokes {@see CnpjFormatterOptions::setHiddenRange} with `mixed` arguments so intentional
                 * invalid-type tests do not trip static analysis (the method body still validates at runtime).
                 */
                function cnpj_formatter_invoke_set_hidden_range(CnpjFormatterOptions $options, mixed $hiddenStart, mixed $hiddenEnd): mixed
                {
                    $reflectedMethod = new ReflectionMethod(CnpjFormatterOptions::class, 'setHiddenRange');

                    return $reflectedMethod->invoke($options, $hiddenStart, $hiddenEnd);
                }

                it('throws CnpjFormatterOptionsTypeError with an object', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, (object) ['not' => 'a number'], 13);
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got object.');
                });

                it('throws CnpjFormatterOptionsTypeError with a string', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, 'not a number', 13);
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got string.');
                });

                it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, true, 13);
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got boolean.');
                });

                it('throws CnpjFormatterOptionsTypeError with a float number', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, 1.5, 13);
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenStart" must be of type integer. Got float number.');
                });
            });

            describe('when setting `hiddenEnd` to a non-integer value', function () {
                it('throws CnpjFormatterOptionsTypeError with an object', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, 0, (object) ['not' => 'a number']);
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got object.');
                });

                it('throws CnpjFormatterOptionsTypeError with a string', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, 0, 'not a number');
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got string.');
                });

                it('throws CnpjFormatterOptionsTypeError with a boolean', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, 0, true);
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got boolean.');
                });

                it('throws CnpjFormatterOptionsTypeError with a float number', function () {
                    $options = new CnpjFormatterOptions();

                    expect(function () use ($options) {
                        cnpj_formatter_invoke_set_hidden_range($options, 0, 1.5);
                    })->toThrow(CnpjFormatterOptionsTypeError::class, 'CNPJ formatting option "hiddenEnd" must be of type integer. Got float number.');
                });
            });
        });
    });
});
