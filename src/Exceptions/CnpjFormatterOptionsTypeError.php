<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj\Exceptions;

use Lacus\Utils\TypeDescriber;

/**
 * Error raised when a specific option in the formatter configuration has an
 * invalid type. The error message includes the option name, the actual input
 * type and the expected type.
 */
class CnpjFormatterOptionsTypeError extends CnpjFormatterTypeError
{
    public readonly string $optionName;

    /**
     * @param 'escape'|'hidden'|'hiddenKey'|'hiddenStart'|'hiddenEnd'|'dotKey'|'slashKey'|'dashKey'|'encode'|'onFail' $optionName
     */
    public function __construct(string $optionName, mixed $actualInput, string $expectedType)
    {
        $actualInputType = TypeDescriber::describe($actualInput);

        parent::__construct(
            $actualInput,
            $actualInputType,
            $expectedType,
            "CNPJ formatting option \"{$optionName}\" must be of type {$expectedType}. Got {$actualInputType}.",
        );
        $this->optionName = $optionName;
    }
}
