<?php declare(strict_types=1);
/**
 * This file is part of the oroshi/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oroshi\Money\ValueObject;

use Daikon\ValueObject\ValueObjectListInterface;
use Daikon\ValueObject\ValueObjectListTrait;

/**
 * @type Oroshi\Money\ValueObject\Money::fromNative
 */
final class Wallet implements ValueObjectListInterface
{
    use ValueObjectListTrait;

    public function hasMoney(): bool
    {
        return $this->reduce(
            fn(bool $carry, Money $money): bool => $carry || !$money->isZero(),
            false
        );
    }

    public function getBalance(Currency $currency): Money
    {
        $index = $this->findByCurrency($currency);
        return  $index !== false ? $this->get($index) : Money::zero($currency);
    }

    public function hasBalance(Money $amount): bool
    {
        $balance = $this->getBalance($amount->getCurrency());
        return $balance->isGreaterThanOrEqual($amount);
    }

    public function credit(Money $amount): self
    {
        $currency = $amount->getCurrency();
        $index = $this->findByCurrency($currency);
        $balance = $this->getBalance($currency);
        $wallet = $index !== false ? $this->without($index) : $this;
        return $wallet->push($balance->add($amount));
    }

    public function debit(Money $amount): self
    {
        $currency = $amount->getCurrency();
        $index = $this->findByCurrency($currency);
        $balance = $this->getBalance($currency);
        $wallet = $index !== false ? $this->without($index) : $this;
        return $wallet->push($balance->add($amount));
    }

    /** @return int|bool */
    private function findByCurrency(Currency $currency)
    {
        return $this->search(fn(Money $money): bool => $money->getCurrency()->equals($currency));
    }
}
