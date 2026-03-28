<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj\Exceptions;

/**
 * Exception raised when a character is not allowed to be used as a key
 * character on options.
 */
class CnpjFormatterOptionsForbiddenKeyCharacterException extends CnpjFormatterException
{
    public readonly string $optionName;
    public readonly string $actualInput;

    /** @var list<string> */
    public readonly array $forbiddenCharacters;

    /**
     * @param 'hiddenKey'|'dotKey'|'slashKey'|'dashKey' $optionName
     * @param list<string> $forbiddenCharacters
     */
    public function __construct(string $optionName, string $actualInput, array $forbiddenCharacters)
    {
        $joined = implode('", "', $forbiddenCharacters);

        parent::__construct("Value \"{$actualInput}\" for CNPJ formatting option \"{$optionName}\" contains disallowed characters (\"{$joined}\").");
        $this->optionName = $optionName;
        $this->actualInput = $actualInput;
        $this->forbiddenCharacters = $forbiddenCharacters;
    }
}
