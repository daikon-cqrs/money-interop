<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\ValueObject;

use Daikon\ValueObject\ValueObjectMapInterface;
use Daikon\ValueObject\ValueObjectMapTrait;

/**
 * @type Daikon\Money\ValueObject\Money::fromNative
 */
final class Wallet implements ValueObjectMapInterface
{
    use ValueObjectMapTrait {
        isEmpty as traitIsEmpty;
    }

    public function isEmpty(): bool
    {
        return $this->traitIsEmpty() || $this->reduce(
            fn(bool $carry, string $currency, MoneyInterface $money): bool => $carry && $money->isZero(),
            true
        );
    }

    public function getBalance(string $currency): MoneyInterface
    {
        /** @var MoneyInterface $balance */
        $balance = $this->get($currency, Money::zero($currency));
        return $balance;
    }

    public function hasBalance(string $currency): bool
    {
        return $this->getBalance($currency)->isPositive();
    }

    public function credit(MoneyInterface $amount): self
    {
        $currency = $amount->getCurrency();
        $balance = $this->getBalance($currency);
        return $this->with($currency, $balance->add($amount));
    }

    public function debit(MoneyInterface $amount): self
    {
        $currency = $amount->getCurrency();
        $balance = $this->getBalance($currency);
        return $this->with($currency, $balance->subtract($amount));
    }
}
