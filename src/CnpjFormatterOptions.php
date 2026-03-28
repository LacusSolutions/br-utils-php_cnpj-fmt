<?php

declare(strict_types=1);

namespace Lacus\CnpjFmt;

use Closure;
use Exception;
use InvalidArgumentException;

class CnpjFormatterOptions
{
    private bool $escape;
    private bool $hidden;
    private string $hiddenKey;
    private int $hiddenStart;
    private int $hiddenEnd;
    private string $dotKey;
    private string $slashKey;
    private string $dashKey;
    private Closure $onFail;

    public function __construct(
        ?bool $escape = null,
        ?bool $hidden = null,
        ?string $hiddenKey = null,
        ?int $hiddenStart = null,
        ?int $hiddenEnd = null,
        ?string $dotKey = null,
        ?string $slashKey = null,
        ?string $dashKey = null,
        ?Closure $onFail = null,
    ) {
        $this->setEscape($escape ?? false);
        $this->setHide($hidden ?? false);
        $this->setHiddenKey($hiddenKey ?? '*');
        $this->setHiddenRange($hiddenStart ?? 5, $hiddenEnd ?? 13);
        $this->setDotKey($dotKey ?? '.');
        $this->setSlashKey($slashKey ?? '/');
        $this->setDashKey($dashKey ?? '-');
        $this->setOnFail($onFail ?? function (string $value): string {
            return $value;
        });
    }

    public function merge(
        ?bool $escape = null,
        ?bool $hidden = null,
        ?string $hiddenKey = null,
        ?int $hiddenStart = null,
        ?int $hiddenEnd = null,
        ?string $dotKey = null,
        ?string $slashKey = null,
        ?string $dashKey = null,
        ?Closure $onFail = null,
    ): self {
        return new self(
            $escape ?? $this->isEscaped(),
            $hidden ?? $this->isHidden(),
            $hiddenKey ?? $this->getHiddenKey(),
            $hiddenStart ?? $this->getHiddenStart(),
            $hiddenEnd ?? $this->getHiddenEnd(),
            $dotKey ?? $this->getDotKey(),
            $slashKey ?? $this->getSlashKey(),
            $dashKey ?? $this->getDashKey(),
            $onFail ?? $this->getOnFail(),
        );
    }

    public function setEscape(bool $value): void
    {
        $this->escape = $value;
    }

    public function isEscaped(): bool
    {
        return $this->escape;
    }

    public function setHide(bool $value): void
    {
        $this->hidden = $value;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHiddenKey(string $value): void
    {
        $this->hiddenKey = $value;
    }

    public function getHiddenKey(): string
    {
        return $this->hiddenKey;
    }

    public function setHiddenRange(int $start, int $end): void
    {
        $min = 0;
        $max = CNPJ_LENGTH - 1;

        if ($start < $min || $start > $max) {
            throw new InvalidArgumentException(
                "Option \"hiddenStart\" must be an integer between {$min} and {$max}."
            );
        }

        if ($end < $min || $end > $max) {
            throw new InvalidArgumentException(
                "Option \"hiddenRange.end\" must be an integer between {$min} and {$max}."
            );
        }

        if ($start > $end) {
            $aux = $start;
            $start = $end;
            $end = $aux;
        }

        $this->hiddenStart = $start;
        $this->hiddenEnd = $end;
    }

    public function getHiddenStart(): int
    {
        return $this->hiddenStart;
    }

    public function getHiddenEnd(): int
    {
        return $this->hiddenEnd;
    }

    public function setDotKey(string $value): void
    {
        $this->dotKey = $value;
    }

    public function getDotKey(): string
    {
        return $this->dotKey;
    }

    public function setSlashKey(string $value): void
    {
        $this->slashKey = $value;
    }

    public function getSlashKey(): string
    {
        return $this->slashKey;
    }

    public function setDashKey(string $value): void
    {
        $this->dashKey = $value;
    }

    public function getDashKey(): string
    {
        return $this->dashKey;
    }

    public function setOnFail(Closure $callback): void
    {
        $this->onFail = $callback;
    }

    /**
     * @return Closure(string, Exception): string
     */
    public function getOnFail(): Closure
    {
        return $this->onFail;
    }
}
