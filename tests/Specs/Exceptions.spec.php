<?php

declare(strict_types=1);

use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputLengthException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputTypeError;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsForbiddenKeyCharacterException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsHiddenRangeInvalidException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterOptionsTypeError;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterTypeError;

describe('CnpjFormatterTypeError', function () {
    describe('when instantiated through a subclass', function () {
        final class TestTypeError extends CnpjFormatterTypeError
        {
        }

        it('is an instance of TypeError', function () {
            $error = new TestTypeError(123, 'number', 'string', 'some error');

            expect($error)->toBeInstanceOf(TypeError::class);
        });

        it('is an instance of CnpjFormatterTypeError', function () {
            $error = new TestTypeError(123, 'number', 'string', 'some error');

            expect($error)->toBeInstanceOf(CnpjFormatterTypeError::class);
        });

        it('sets the `actualInput` property', function () {
            $error = new TestTypeError(123, 'number', 'string', 'some error');

            expect($error->actualInput)->toBe(123);
        });

        it('sets the `actualType` property', function () {
            $error = new TestTypeError(123, 'number', 'string', 'some error');

            expect($error->actualType)->toBe('number');
        });

        it('sets the `expectedType` property', function () {
            $error = new TestTypeError(123, 'number', 'string', 'some error');

            expect($error->expectedType)->toBe('string');
        });

        it('has the correct message', function () {
            $error = new TestTypeError(123, 'number', 'string', 'some error');

            expect($error->getMessage())->toBe('some error');
        });

        it('has the correct name', function () {
            $error = new TestTypeError(123, 'number', 'string', 'some error');

            expect($error->getName())->toBe('TestTypeError');
        });
    });
});

describe('CnpjFormatterInputTypeError', function () {
    describe('when instantiated', function () {
        it('is an instance of TypeError', function () {
            $error = new CnpjFormatterInputTypeError(123, 'string');

            expect($error)->toBeInstanceOf(TypeError::class);
        });

        it('is an instance of CnpjFormatterTypeError', function () {
            $error = new CnpjFormatterInputTypeError(123, 'string');

            expect($error)->toBeInstanceOf(CnpjFormatterTypeError::class);
        });

        it('sets the `actualInput` property', function () {
            $error = new CnpjFormatterInputTypeError(123, 'string');

            expect($error->actualInput)->toBe(123);
        });

        it('sets the `actualType` property', function () {
            $error = new CnpjFormatterInputTypeError(123, 'string');

            expect($error->actualType)->toBe('integer number');
        });

        it('sets the `expectedType` property', function () {
            $error = new CnpjFormatterInputTypeError(123, 'string or string[]');

            expect($error->expectedType)->toBe('string or string[]');
        });

        it('has the correct message', function () {
            $actualInput = 123;
            $actualType = 'integer number';
            $expectedType = 'string or string[]';
            $message = "CNPJ input must be of type {$expectedType}. Got {$actualType}.";

            $error = new CnpjFormatterInputTypeError($actualInput, $expectedType);

            expect($error->getMessage())->toBe($message);
        });

        it('has the correct name', function () {
            $error = new CnpjFormatterInputTypeError(123, 'string or string[]');

            expect($error->getName())->toBe('CnpjFormatterInputTypeError');
        });
    });
});

describe('CnpjFormatterOptionsTypeError', function () {
    describe('when instantiated', function () {
        it('is an instance of TypeError', function () {
            $error = new CnpjFormatterOptionsTypeError('hidden', 123, 'boolean');

            expect($error)->toBeInstanceOf(TypeError::class);
        });

        it('is an instance of CnpjFormatterTypeError', function () {
            $error = new CnpjFormatterOptionsTypeError('hidden', 123, 'boolean');

            expect($error)->toBeInstanceOf(CnpjFormatterTypeError::class);
        });

        it('sets the `optionName` property', function () {
            $error = new CnpjFormatterOptionsTypeError('hiddenKey', 123, 'boolean');

            expect($error->optionName)->toBe('hiddenKey');
        });

        it('sets the `actualInput` property', function () {
            $error = new CnpjFormatterOptionsTypeError('hiddenKey', 123, 'boolean');

            expect($error->actualInput)->toBe(123);
        });

        it('sets the `actualType` property', function () {
            $error = new CnpjFormatterOptionsTypeError('hiddenKey', 123, 'boolean');

            expect($error->actualType)->toBe('integer number');
        });

        it('sets the `expectedType` property', function () {
            $error = new CnpjFormatterOptionsTypeError('hiddenKey', 123, 'boolean');

            expect($error->expectedType)->toBe('boolean');
        });

        it('has the correct message', function () {
            $optionName = 'hiddenKey';
            $actualInput = 123;
            $actualInputType = 'integer number';
            $expectedType = 'boolean';
            $message = "CNPJ formatting option \"{$optionName}\" must be of type {$expectedType}. Got {$actualInputType}.";

            $error = new CnpjFormatterOptionsTypeError($optionName, $actualInput, $expectedType);

            expect($error->getMessage())->toBe($message);
        });

        it('has the correct name', function () {
            $error = new CnpjFormatterOptionsTypeError('hiddenKey', 123, 'boolean');

            expect($error->getName())->toBe('CnpjFormatterOptionsTypeError');
        });
    });
});

describe('CnpjFormatterException', function () {
    describe('when instantiated through a subclass', function () {
        final class TestException extends CnpjFormatterException
        {
        }

        it('is an instance of Exception', function () {
            $exception = new TestException('some error');

            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('is an instance of CnpjFormatterException', function () {
            $exception = new TestException('some error');

            expect($exception)->toBeInstanceOf(CnpjFormatterException::class);
        });

        it('has the correct message', function () {
            $exception = new TestException('some exception');

            expect($exception->getMessage())->toBe('some exception');
        });

        it('has the correct name', function () {
            $exception = new TestException('some error');

            expect($exception->getName())->toBe('TestException');
        });
    });
});

describe('CnpjFormatterInputLengthException', function () {
    describe('when instantiated', function () {
        it('is an instance of Exception', function () {
            $exception = new CnpjFormatterInputLengthException('1.2.3.4.5', '12345', 14);

            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('is an instance of CnpjFormatterException', function () {
            $exception = new CnpjFormatterInputLengthException('1.2.3.4.5', '12345', 14);

            expect($exception)->toBeInstanceOf(CnpjFormatterException::class);
        });

        it('sets the `actualInput` property', function () {
            $exception = new CnpjFormatterInputLengthException('1.2.3.4.5', '12345', 14);

            expect($exception->actualInput)->toBe('1.2.3.4.5');
        });

        it('sets the `evaluatedInput` property', function () {
            $exception = new CnpjFormatterInputLengthException('1.2.3.4.5', '12345', 14);

            expect($exception->evaluatedInput)->toBe('12345');
        });

        it('sets the `expectedLength` property', function () {
            $exception = new CnpjFormatterInputLengthException('1.2.3.4.5', '12345', 14);

            expect($exception->expectedLength)->toBe(14);
        });

        it('has the correct message', function () {
            $actualInput = '1.2.3.4.5';
            $evaluatedInput = '12345';
            $expectedLength = 14;
            $message = "CNPJ input \"{$actualInput}\" does not contain {$expectedLength} characters. Got " . strlen($evaluatedInput) . " in \"{$evaluatedInput}\".";

            $exception = new CnpjFormatterInputLengthException($actualInput, $evaluatedInput, $expectedLength);

            expect($exception->getMessage())->toBe($message);
        });

        it('has the correct name', function () {
            $exception = new CnpjFormatterInputLengthException('1.2.3.4.5', '12345', 14);

            expect($exception->getName())->toBe('CnpjFormatterInputLengthException');
        });
    });
});

describe('CnpjFormatterOptionsHiddenRangeInvalidException', function () {
    describe('when instantiated', function () {
        it('is an instance of Exception', function () {
            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException('hiddenStart', 123, 0, 13);

            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('is an instance of CnpjFormatterException', function () {
            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException('hiddenStart', 123, 0, 13);

            expect($exception)->toBeInstanceOf(CnpjFormatterException::class);
        });

        it('sets the `optionName` property', function () {
            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException('hiddenStart', 123, 0, 13);

            expect($exception->optionName)->toBe('hiddenStart');
        });

        it('sets the `actualInput` property', function () {
            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException('hiddenStart', 123, 0, 13);

            expect($exception->actualInput)->toBe(123);
        });

        it('sets the `minExpectedValue` property', function () {
            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException('hiddenStart', 123, 0, 13);

            expect($exception->minExpectedValue)->toBe(0);
        });

        it('sets the `maxExpectedValue` property', function () {
            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException('hiddenStart', 123, 0, 13);

            expect($exception->maxExpectedValue)->toBe(13);
        });

        it('has the correct message', function () {
            $optionName = 'hiddenStart';
            $actualInput = 123;
            $minExpectedValue = 5;
            $maxExpectedValue = 13;
            $message = "CNPJ formatting option \"{$optionName}\" must be an integer between {$minExpectedValue} and {$maxExpectedValue}. Got {$actualInput}.";

            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException($optionName, $actualInput, $minExpectedValue, $maxExpectedValue);

            expect($exception->getMessage())->toBe($message);
        });

        it('has the correct name', function () {
            $exception = new CnpjFormatterOptionsHiddenRangeInvalidException('hiddenStart', 123, 0, 13);

            expect($exception->getName())->toBe('CnpjFormatterOptionsHiddenRangeInvalidException');
        });
    });
});

describe('CnpjFormatterOptionsForbiddenKeyCharacterException', function () {
    describe('when instantiated', function () {
        it('is an instance of Exception', function () {
            $exception = new CnpjFormatterOptionsForbiddenKeyCharacterException('hiddenKey', 'x', ['x']);

            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('is an instance of CnpjFormatterException', function () {
            $exception = new CnpjFormatterOptionsForbiddenKeyCharacterException('hiddenKey', 'x', ['x']);

            expect($exception)->toBeInstanceOf(CnpjFormatterException::class);
        });

        it('sets the `optionName` property', function () {
            $exception = new CnpjFormatterOptionsForbiddenKeyCharacterException('hiddenKey', 'x', ['x']);

            expect($exception->optionName)->toBe('hiddenKey');
        });

        it('sets the `actualInput` property', function () {
            $exception = new CnpjFormatterOptionsForbiddenKeyCharacterException('hiddenKey', 'x', ['x']);

            expect($exception->actualInput)->toBe('x');
        });

        it('sets the `forbiddenCharacters` property', function () {
            $exception = new CnpjFormatterOptionsForbiddenKeyCharacterException('hiddenKey', 'x', ['x']);

            expect($exception->forbiddenCharacters)->toBe(['x']);
        });

        it('has the correct message', function () {
            $optionName = 'hiddenKey';
            $actualInput = 'x';
            $forbiddenCharacters = ['x'];
            $message = "Value \"{$actualInput}\" for CNPJ formatting option \"{$optionName}\" contains disallowed characters (\"{$forbiddenCharacters[0]}\").";

            $exception = new CnpjFormatterOptionsForbiddenKeyCharacterException($optionName, $actualInput, $forbiddenCharacters);

            expect($exception->getMessage())->toBe($message);
        });

        it('has the correct name', function () {
            $exception = new CnpjFormatterOptionsForbiddenKeyCharacterException('hiddenKey', 'x', ['x']);

            expect($exception->getName())->toBe('CnpjFormatterOptionsForbiddenKeyCharacterException');
        });
    });
});
