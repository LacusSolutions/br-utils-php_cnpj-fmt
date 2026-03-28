<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj\Exceptions;

/**
 * Exception raised when the CNPJ string input (after optional processing) does
 * not have the required length. A valid CNPJ must contain exactly 14
 * alphanumeric characters. The error message distinguishes between the original
 * input and the evaluated one (which strips punctuation characters).
 */
class CnpjFormatterInputLengthException extends CnpjFormatterException
{
    /** @var string|list<string> */
    public readonly string|array $actualInput;
    public readonly string $evaluatedInput;
    public readonly int $expectedLength;

    /**
     * @param string|list<string> $actualInput
     */
    public function __construct(string|array $actualInput, string $evaluatedInput, int $expectedLength)
    {
        $fmtActualInput = is_string($actualInput)
            ? "\"{$actualInput}\""
            : json_encode($actualInput, JSON_THROW_ON_ERROR);
        $fmtEvaluatedInput = $actualInput === $evaluatedInput
            ? (string) strlen($evaluatedInput)
            : strlen($evaluatedInput) . ' in "' . $evaluatedInput . '"';

        parent::__construct("CNPJ input {$fmtActualInput} does not contain {$expectedLength} characters. Got {$fmtEvaluatedInput}.");
        $this->actualInput = $actualInput;
        $this->evaluatedInput = $evaluatedInput;
        $this->expectedLength = $expectedLength;
    }
}
