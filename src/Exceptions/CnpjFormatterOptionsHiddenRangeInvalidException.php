<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj\Exceptions;

/**
 * Exception raised when `hiddenStart` or `hiddenEnd` option values are outside
 * the valid range for CNPJ formatting. The valid range bounds are typically
 * between 0 and 13 (inclusive), representing the indices of the 14-character
 * CNPJ string. The error message includes the option name, the actual input
 * value, and the expected range bounds.
 */
class CnpjFormatterOptionsHiddenRangeInvalidException extends CnpjFormatterException
{
    public readonly string $optionName;
    public readonly int $actualInput;
    public readonly int $minExpectedValue;
    public readonly int $maxExpectedValue;

    /**
     * @param 'hiddenStart'|'hiddenEnd' $optionName
     */
    public function __construct(
        string $optionName,
        int $actualInput,
        int $minExpectedValue,
        int $maxExpectedValue,
    ) {
        parent::__construct("CNPJ formatting option \"{$optionName}\" must be an integer between {$minExpectedValue} and {$maxExpectedValue}. Got {$actualInput}.");
        $this->optionName = $optionName;
        $this->actualInput = $actualInput;
        $this->minExpectedValue = $minExpectedValue;
        $this->maxExpectedValue = $maxExpectedValue;
    }
}
