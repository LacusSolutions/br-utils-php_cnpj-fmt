<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj\Exceptions;

use TypeError;

/**
 * Base error for all `cnpj-fmt` type-related errors.
 *
 * This abstract class extends the native `TypeError` and serves as the base for
 * all type validation errors in the CNPJ formatter.
 */
abstract class CnpjFormatterTypeError extends TypeError
{
    public readonly mixed $actualInput;
    public readonly string $actualType;
    public readonly string $expectedType;

    public function __construct(
        mixed $actualInput,
        string $actualType,
        string $expectedType,
        string $message,
    ) {
        parent::__construct($message);
        $this->actualInput = $actualInput;
        $this->actualType = $actualType;
        $this->expectedType = $expectedType;
    }

    /**
     * Get the short class name of the error instance.
     */
    public function getName(): string
    {
        return static::class;
    }
}
