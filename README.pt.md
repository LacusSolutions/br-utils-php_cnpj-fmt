![cnpj-fmt para PHP](https://br-utils.vercel.app/img/cover_cnpj-fmt.jpg)

> 🚀 **Suporte total ao [novo formato alfanumérico de CNPJ](https://github.com/user-attachments/files/23937961/calculodvcnpjalfanaumerico.pdf).**

> 🌎 [Access documentation in English](https://github.com/LacusSolutions/br-utils-php/blob/main/packages/cnpj-fmt/README.md)

Utilitário em PHP para formatar CNPJ (Cadastro Nacional da Pessoa Jurídica) como valor alfanumérico de 14 caracteres, com opções de máscara, escape HTML e codificação para URL.

## Recursos

- ✅ **CNPJ alfanumérico**: Suporte completo ao novo formato alfanumérico de CNPJ (a partir de 2026)
- ✅ **Entrada flexível**: Aceita `string` ou `list<string>`; elementos do array são concatenados na ordem
- ✅ **Agnóstico ao formato**: Remove caracteres não alfanuméricos da entrada em string e converte letras para maiúsculas
- ✅ **Delimitadores personalizáveis**: `dotKey`, `slashKey` e `dashKey` podem ser vazios ou strings de um ou vários caracteres
- ✅ **Mascaramento**: Ocultação opcional de um intervalo de índices com string de substituição configurável (`hidden`, `hiddenKey`, `hiddenStart`, `hiddenEnd`)
- ✅ **Saída HTML e URL**: `escape` opcional (entidades HTML) e `encode` opcional (codificação tipo componente de URI, semelhante ao `encodeURIComponent` do JavaScript)
- ✅ **Erro de tamanho sem exceção**: Comprimento inválido após sanitização é tratado via `onFail` (o padrão retorna string vazia)
- ✅ **Dependências mínimas**: Apenas [`lacus/utils`](https://packagist.org/packages/lacus/utils)
- ✅ **Tratamento de erros**: Erros de tipo para uso incorreto da API; validação de opções com exceções específicas

## Instalação

```bash
# usando Composer
$ composer require lacus/cnpj-fmt
```

## Importação

```php
<?php

use Lacus\BrUtils\Cnpj\CnpjFormatter;
use Lacus\BrUtils\Cnpj\CnpjFormatterOptions;

use function Lacus\BrUtils\Cnpj\cnpj_fmt;
```

O arquivo autoload `cnpj-fmt.php` também define a constante global `Lacus\BrUtils\Cnpj\CNPJ_LENGTH` (`14`), alinhada a `CnpjFormatterOptions::CNPJ_LENGTH`.

## Início rápido

```php
<?php

use Lacus\BrUtils\Cnpj\CnpjFormatter;

$formatter = new CnpjFormatter();

$formatter->format('03603568000195');   // '03.603.568/0001-95'
$formatter->format('12ABC34500DE99');   // '12.ABC.345/00DE-99'
```

## Utilização

Os pontos principais são a classe `CnpjFormatter`, o objeto de valor `CnpjFormatterOptions` e o helper `cnpj_fmt()`.

### `CnpjFormatter`

- **`__construct`**: Opções padrão de formatação. O primeiro parâmetro pode ser `null` ou uma instância de `CnpjFormatterOptions` (essa instância é armazenada; alterações posteriores afetam chamadas a `format` que não passarem opções por chamada). Também é possível passar campos como argumentos nomeados (`hidden`, `hiddenKey`, `dotKey`, …). Se o primeiro argumento não for uma instância de `CnpjFormatterOptions`, é criado um novo `CnpjFormatterOptions` a partir desses valores nomeados. Exemplo: `new CnpjFormatter(hidden: true, slashKey: '|')`.
- **`getOptions()`**: Retorna o `CnpjFormatterOptions` da instância (o mesmo objeto usado internamente).
- **`format`**: `format(string|list<string> $cnpjInput, ?CnpjFormatterOptions|array $options, …opções nomeadas…): string`

  A entrada é normalizada removendo caracteres não alfanuméricos e convertendo para maiúsculas. Se o comprimento após sanitização não for exatamente **14**, o callback **`onFail`** é chamado com a entrada original e uma `CnpjFormatterInputLengthException`; o valor de retorno do callback é o resultado (nada é lançado por comprimento).

  Se a entrada não for `string` nem `list` de strings, é lançada **`CnpjFormatterInputTypeError`**.

  As opções por chamada são mescladas sobre os padrões da instância apenas naquela chamada (os padrões da instância não mudam). É possível passar uma instância de `CnpjFormatterOptions` ou um array associativo como segundo argumento, além de parâmetros nomeados; sobrescritas posteriores prevalecem.

### `CnpjFormatterOptions`

Armazena todas as configurações do formatador. Construa com parâmetros nomeados e `overrides` opcional (lista de arrays e/ou outras instâncias de `CnpjFormatterOptions`, mescladas em ordem). Expõe propriedades via `__get` / `__set` mágicos (`hidden`, `hiddenKey`, `hiddenStart`, `hiddenEnd`, `dotKey`, `slashKey`, `dashKey`, `escape`, `encode`, `onFail`).

- **`getAll()`**: Retorna um array superficial com todas as opções.
- **`set(...)`**: Atualiza vários campos de uma vez; retorna `$this`.
- **`setHiddenRange(?int $hiddenStart, ?int $hiddenEnd)`**: Valida índices em **`[0, 13]`** (inclusivos); se `hiddenStart > hiddenEnd`, os valores são trocados. Argumentos `null` usam os padrões (`DEFAULT_HIDDEN_START` / `DEFAULT_HIDDEN_END`).
- **`getDefaultOnFail()`**: Retorna o closure padrão de `onFail` do pacote (retorna `''` para comprimento inválido).

**`hiddenStart` / `hiddenEnd`**: Os índices referem-se à **string CNPJ normalizada de 14 caracteres** (antes de inserir pontuação). O intervalo inclusivo é substituído internamente por placeholders e depois por `hiddenKey` (permite chaves com vários caracteres ou string vazia).

**Opções de chave** (`hiddenKey`, `dotKey`, `slashKey`, `dashKey`): Devem ser strings e não podem conter caracteres em `CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS` (reservados para a lógica interna).

### Helper funcional

`cnpj_fmt()` instancia um novo `CnpjFormatter` com os mesmos parâmetros do construtor (a partir das opções opcionais / argumentos nomeados) e chama `format($cnpjInput)` uma vez. Use argumentos nomeados para as opções: por exemplo `cnpj_fmt($cnpj, hidden: true)` ou `cnpj_fmt($cnpj, slashKey: '|')`.

```php
$cnpj = '03603568000195';

cnpj_fmt($cnpj);                 // '03.603.568/0001-95'
cnpj_fmt($cnpj, hidden: true);   // mascarado com padrões
cnpj_fmt(                        // '03603568|0001_95'
  $cnpj,
  dotKey: '',
  slashKey: '|',
  dashKey: '_',
);
```

### Exemplos orientados a objeto

```php
$formatter = new CnpjFormatter();
$cnpj = '03603568000195';

$formatter->format($cnpj);   // '03.603.568/0001-95'
$formatter->format(          // '03.603.###/####-##'
    $cnpj,
    hidden: true,
    hiddenKey: '#',
    hiddenStart: 5,
    hiddenEnd: 13,
);
```

Opções padrão na instância; sobrescritas por chamada:

```php
$formatter = new CnpjFormatter(hidden: true);

$formatter->format($cnpj);                  // usa mascaramento da instância
$formatter->format($cnpj, hidden: false);   // só esta chamada: sem máscara
$formatter->format($cnpj);                  // volta aos padrões da instância
```

### Formatos de entrada

**String:** dígitos e/ou letras crus, ou CNPJ já formatado (ex.: `12.345.678/0009-10`, `12.ABC.345/00DE-99`). Caracteres não alfanuméricos são removidos; letras minúsculas viram maiúsculas.

**Array de strings:** cada elemento deve ser string; os valores são concatenados (por dígito, grupos ou misturados com pontuação — tudo é removido na normalização). Elementos que não são strings não são permitidos.

### Opções de formatação

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `hidden` | `?bool` | `false` | Se `true`, substitui o intervalo inclusivo `[hiddenStart, hiddenEnd]` na string normalizada de 14 caracteres antes de aplicar a pontuação |
| `hiddenKey` | `?string` | `'*'` | Substituição para cada posição oculta (pode ser multi-caractere ou vazia); não pode usar caracteres de chave proibidos |
| `hiddenStart` | `?int` | `5` | Índice inicial `0`–`13` (inclusivo) |
| `hiddenEnd` | `?int` | `13` | Índice final `0`–`13` (inclusivo); se `hiddenStart > hiddenEnd`, são trocados |
| `dotKey` | `?string` | `'.'` | Separador entre os grupos `XX` / `XXX` / `XXX` |
| `slashKey` | `?string` | `'/'` | Separador antes do bloco da filial |
| `dashKey` | `?string` | `'-'` | Separador antes dos dois últimos caracteres |
| `escape` | `?bool` | `false` | Se `true`, aplica escape HTML na string final (`HtmlUtils::escape`) |
| `encode` | `?bool` | `false` | Se `true`, codifica a string final para URL (`UrlUtils::encodeUriComponent`) |
| `onFail` | `?\Closure` | ver abaixo | `Closure(mixed $value, CnpjFormatterException $e): string` — usado quando o comprimento sanitizado ≠ 14 |

O **`onFail`** padrão retorna string vazia. A exceção passada em falhas de comprimento é **`CnpjFormatterInputLengthException`** (`actualInput`, `evaluatedInput`, `expectedLength`).

### Erros e exceções

- **Tipo de entrada incorreto** (não é `string` nem `list<string>`): **`CnpjFormatterInputTypeError`** — estende **`CnpjFormatterTypeError`** (estende `TypeError` do PHP).
- **Tipos ou valores de opção inválidos ao construir ou mesclar opções**: **`CnpjFormatterOptionsTypeError`**, **`CnpjFormatterOptionsHiddenRangeInvalidException`**, **`CnpjFormatterOptionsForbiddenKeyCharacterException`** — estendem **`CnpjFormatterTypeError`** ou **`CnpjFormatterException`** conforme o caso.

Incompatibilidade de comprimento **não** é lançada por `format()`; trate em **`onFail`**.

```php
use Lacus\BrUtils\Cnpj\CnpjFormatter;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputLengthException;
use Lacus\BrUtils\Cnpj\Exceptions\CnpjFormatterInputTypeError;

try {
    (new CnpjFormatter())->format(12345);
} catch (CnpjFormatterInputTypeError $e) {
    // entrada deve ser string ou string[]
}

$out = (new CnpjFormatter())->format(
    'short',
    onFail: static fn ($value, CnpjFormatterInputLengthException $e) => 'invalid'
);
```

### Outros recursos disponíveis

- **`CNPJ_LENGTH`**: `14` — `CnpjFormatterOptions::CNPJ_LENGTH`, e constante global `Lacus\BrUtils\Cnpj\CNPJ_LENGTH` quando `cnpj-fmt.php` é carregado pelo autoload do Composer.
- **`CnpjFormatterOptions::DISALLOWED_KEY_CHARACTERS`**: Caracteres proibidos em `hiddenKey`, `dotKey`, `slashKey`, `dashKey`.
- **`CnpjFormatterOptions::getDefaultOnFail()`**: Callback padrão de falha compartilhado.

## Contribuição e suporte

Contribuições são bem-vindas! Consulte as [Diretrizes de contribuição](https://github.com/LacusSolutions/br-utils-php/blob/main/CONTRIBUTING.md). Se o projeto for útil para você, considere:

- ⭐ Dar uma estrela no repositório
- 🤝 Contribuir com código
- 💡 [Sugerir novas funcionalidades](https://github.com/LacusSolutions/br-utils-php/issues)
- 🐛 [Reportar bugs](https://github.com/LacusSolutions/br-utils-php/issues)

## Licença

Este projeto está sob a licença MIT — veja o arquivo [LICENSE](https://github.com/LacusSolutions/br-utils-php/blob/main/LICENSE).

## Changelog

Veja o [CHANGELOG](https://github.com/LacusSolutions/br-utils-php/blob/main/packages/cnpj-fmt/CHANGELOG.md) para alterações e histórico de versões.

---

Feito com ❤️ por [Lacus Solutions](https://github.com/LacusSolutions)
