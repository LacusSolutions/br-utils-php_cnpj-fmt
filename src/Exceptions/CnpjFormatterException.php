<?php

declare(strict_types=1);

namespace Lacus\BrUtils\Cnpj\Exceptions;

use Exception;

/**
 * Base exception for all `cnpj-fmt` rules-related errors.
 *
 * This abstract class extends the native `Exception` and serves as the base for
 * all non-type-related errors in the `CnpjFormatter` and its dependencies. It is
 * suitable for validation errors, range errors, and other business logic
 * exceptions that are not strictly type-related.
 */
abstract class CnpjFormatterException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Get the short class name of the exception instance.
     */
    public function getName(): string
    {
        return static::class;
    }
}
