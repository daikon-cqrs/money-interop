<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Service;

use Daikon\Interop\Assertion;
use Daikon\Money\ValueObject\Money;
use Daikon\Money\ValueObject\MoneyInterface;
use Money\Converter;
use Money\Currency as PhpCurrency;
use Money\Money as PhpMoney;
use Money\MoneyFormatter;
use Money\MoneyParser;

final class MoneyService
{
    private MoneyParser $parser;

    private Converter $converter;

    private MoneyFormatter $formatter;

    private string $moneyType;

    public function __construct(
        MoneyParser $parser,
        Converter $converter,
        MoneyFormatter $formatter,
        string $moneyType = null
    ) {
        $this->parser = $parser;
        $this->converter = $converter;
        $this->formatter = $formatter;
        Assertion::implementsInterface($moneyType, MoneyInterface::class, 'Not a valid money implementation.');
        $this->moneyType = $moneyType ?? Money::class;
    }

    public function parse(string $amount, string $currency = null): MoneyInterface
    {
        $parsed = $this->parser->parse($amount, $currency);
        return $this->moneyType::fromNative($parsed->getAmount().$parsed->getCurrency());
    }

    public function format(MoneyInterface $money): string
    {
        return $this->formatter->format(
            new PhpMoney($money->getAmount(), new PhpCurrency($money->getCurrency()))
        );
    }

    public function convert(MoneyInterface $money, string $currency): MoneyInterface
    {
        $converted = $this->converter->convert(
            new PhpMoney($money->getAmount(), new PhpCurrency($money->getCurrency())),
            new PhpCurrency($currency)
        );
        return $this->moneyType::fromNative($converted->getAmount().$converted->getCurrency());
    }
}
