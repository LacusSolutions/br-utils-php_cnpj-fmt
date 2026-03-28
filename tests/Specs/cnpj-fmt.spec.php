<?php

declare(strict_types=1);

use function Lacus\BrUtils\Cnpj\cnpj_fmt;

use Lacus\BrUtils\Cnpj\CnpjFormatter;

describe('cnpj_fmt', function () {
    describe('when called', function () {
        it('matches CnpjFormatter->format behavior', function () {
            $input = '91415732000793';
            $formatter = new CnpjFormatter();

            expect(cnpj_fmt($input))->toBe($formatter->format($input));
        });

        it('accepts options and forwards formatting behavior', function () {
            $input = '01ABC234000X56';
            $options = ['slashKey' => '|'];

            expect(cnpj_fmt($input, ...$options))->toBe('01.ABC.234|000X-56');
        });
    });
});
