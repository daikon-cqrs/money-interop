<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\ValueObject;

use Daikon\Interop\Assertion;
use Daikon\Interop\InvalidArgumentException;
use Daikon\Money\ValueObject\MoneyInterface;
use Money\Currency as PhpCurrency;
use Money\Money as PhpMoney;

class Money implements MoneyInterface
{
    protected PhpMoney $money;

    /** @param static $comparator */
    public function equals($comparator): bool
    {
        Assertion::isInstanceOf($comparator, static::class);
        return $this->toNative() === $comparator->toNative();
    }

    public function getAmount(): string
    {
        return $this->money->getAmount();
    }

    public function getCurrency(): string
    {
        return $this->money->getCurrency()->getCode();
    }

    /** @return static */
    public function multiply($multiplier, int $roundingMode = self::ROUND_HALF_UP): self
    {
        Assertion::numeric($multiplier, 'Multipler must be numeric.');
        $multiplied = $this->money->multiply($multiplier, $roundingMode);
        return new static($multiplied);
    }

    /** @return static */
    public function divide($divisor, int $roundingMode = self::ROUND_HALF_UP): self
    {
        Assertion::numeric($divisor, 'Divider must be numeric.');
        $divided = $this->money->divide($divisor, $roundingMode);
        return new static($divided);
    }

    /** @return static */
    public function percentage($percentage, int $roundingMode = self::ROUND_HALF_UP): self
    {
        return $this->multiply($percentage)->divide(100, $roundingMode);
    }

    /** @return static */
    public function add(MoneyInterface $money): self
    {
        $added = $this->money->add(
            static::asBaseMoney($money->getAmount(), $money->getCurrency())
        );
        return new static($added);
    }

    /** @return static */
    public function subtract(MoneyInterface $money): self
    {
        $subtracted = $this->money->subtract(
            static::asBaseMoney($money->getAmount(), $money->getCurrency())
        );
        return new static($subtracted);
    }

    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    public function isPositive(): bool
    {
        return $this->money->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->money->isNegative();
    }

    public function isLessThanOrEqual(MoneyInterface $money): bool
    {
        return $this->money->lessThanOrEqual(
            static::asBaseMoney($money->getAmount(), $money->getCurrency())
        );
    }

    public function isGreaterThanOrEqual(MoneyInterface $money): bool
    {
        return $this->money->greaterThanOrEqual(
            static::asBaseMoney($money->getAmount(), $money->getCurrency())
        );
    }

    /**
     * @param string $value
     * @return static
     */
    public static function fromNative($value): self
    {
        Assertion::string($value, 'Must be a string.');
        if (!preg_match('/^(?<amount>-?\d+)\s?(?<currency>[a-z][a-z0-9]*)$/i', $value, $matches)) {
            throw new InvalidArgumentException('Invalid amount.');
        }

        return new static(static::asBaseMoney($matches['amount'], $matches['currency']));
    }

    /** @return static */
    public static function zero($currency = null): self
    {
        Assertion::regex($currency, '/^[a-z][a-z0-9]*$/i', 'Invalid currency.');
        return static::fromNative('0'.(string)$currency);
    }

    public function toNative(): string
    {
        return $this->getAmount().$this->getCurrency();
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    protected static function asBaseMoney(string $amount, string $currency): PhpMoney
    {
        return new PhpMoney($amount, new PhpCurrency($currency));
    }

    final protected function __construct(PhpMoney $money)
    {
        $this->money = $money;
    }
}
