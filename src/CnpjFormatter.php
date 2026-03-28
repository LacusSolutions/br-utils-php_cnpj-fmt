<?php

declare(strict_types=1);

namespace Lacus\CnpjFmt;

use Closure;
use InvalidArgumentException;

class CnpjFormatter
{
    private CnpjFormatterOptions $options;

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
        $this->options = new CnpjFormatterOptions(
            $escape,
            $hidden,
            $hiddenKey,
            $hiddenStart,
            $hiddenEnd,
            $dotKey,
            $slashKey,
            $dashKey,
            $onFail,
        );
    }

    public function format(
        string $cnpjString,
        ?bool $escape = null,
        ?bool $hidden = null,
        ?string $hiddenKey = null,
        ?int $hiddenStart = null,
        ?int $hiddenEnd = null,
        ?string $dotKey = null,
        ?string $slashKey = null,
        ?string $dashKey = null,
        ?Closure $onFail = null,
    ): string {
        $actualOptions = $this->getOptions()->merge(
            $escape,
            $hidden,
            $hiddenKey,
            $hiddenStart,
            $hiddenEnd,
            $dotKey,
            $slashKey,
            $dashKey,
            $onFail,
        );

        $cnpjNumbersString = preg_replace('/[^0-9]/', '', $cnpjString) ?? '';
        $cnpjNumbersArray = str_split($cnpjNumbersString);

        if (count($cnpjNumbersArray) !== CNPJ_LENGTH) {
            $error = new InvalidArgumentException(
                "Parameter \"{$cnpjString}\" does not contain "
                . CNPJ_LENGTH
                . " digits."
            );

            return $actualOptions->getOnFail()($cnpjString, $error);
        }

        if ($actualOptions->isHidden()) {
            $hiddenStart = $actualOptions->getHiddenStart();
            $hiddenEnd = $actualOptions->getHiddenEnd();
            $hiddenKey = $actualOptions->getHiddenKey();

            for ($i = $hiddenStart; $i <= $hiddenEnd; $i++) {
                $cnpjNumbersArray[$i] = $hiddenKey;
            }
        }

        $dotKey = $actualOptions->getDotKey();
        $dashKey = $actualOptions->getDashKey();
        $slashKey = $actualOptions->getSlashKey();

        array_splice($cnpjNumbersArray, 12, 0, $dashKey);
        array_splice($cnpjNumbersArray, 8, 0, $slashKey);
        array_splice($cnpjNumbersArray, 5, 0, $dotKey);
        array_splice($cnpjNumbersArray, 2, 0, $dotKey);

        $prettyCnpj = implode('', $cnpjNumbersArray);

        if ($actualOptions->isEscaped()) {
            return htmlspecialchars($prettyCnpj, ENT_QUOTES, 'UTF-8');
        }

        return $prettyCnpj;
    }

    public function getOptions(): CnpjFormatterOptions
    {
        return $this->options;
    }
}
