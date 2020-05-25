<?php declare(strict_types=1);
/**
 * This file is part of the oroshi/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oroshi\Money\ValueObject;

use Assert\Assertion;
use Daikon\ValueObject\ValueObjectInterface;
use InvalidArgumentException;
use Money\Currency as PhpCurrency;
use Money\Money as PhpMoney;

final class Money implements ValueObjectInterface
{
    public const ROUND_UP = PhpMoney::ROUND_UP;
    public const ROUND_DOWN = PhpMoney::ROUND_DOWN;
    public const ROUND_HALF_UP = PhpMoney::ROUND_HALF_UP;
    public const ROUND_HALF_DOWN = PhpMoney::ROUND_HALF_DOWN;

    private PhpMoney $money;

    /** @param self $comparator */
    public function equals($comparator): bool
    {
        Assertion::isInstanceOf($comparator, self::class);
        return $this->toNative() === $comparator->toNative();
    }

    public function getAmount(): string
    {
        return $this->money->getAmount();
    }

    public function getCurrency(): Currency
    {
        return Currency::fromNative($this->money->getCurrency()->getCode());
    }

    /** @param float|int|string $multiplier */
    public function multiply($multiplier, int $roundingMode = self::ROUND_HALF_UP): self
    {
        Assertion::numeric($multiplier, 'Multipler must be numeric.');
        $multiplied = $this->money->multiply($multiplier, $roundingMode);
        return new self($multiplied);
    }

    /** @param float|int|string $divisor */
    public function divide($divisor, int $roundingMode = self::ROUND_HALF_UP): self
    {
        Assertion::numeric($divisor, 'Divider must be numeric.');
        $divided = $this->money->divide($divisor, $roundingMode);
        return new self($divided);
    }

    public function add(self $money): self
    {
        $added = $this->money->add($money->unwrap());
        return new self($added);
    }

    public function subtract(self $money): self
    {
        $subtracted = $this->money->subtract($money->unwrap());
        return new self($subtracted);
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

    public function isLessThanOrEqual(self $money): bool
    {
        return $this->money->lessThanOrEqual($money->unwrap());
    }

    public function isGreaterThanOrEqual(self $money): bool
    {
        return $this->money->greaterThanOrEqual($money->unwrap());
    }

    /** @param string $value */
    public static function fromNative($value): self
    {
        Assertion::string($value, 'Must be a string.');
        if (!preg_match('#^(?<amount>-?[0-9]+)(?<currency>[a-z]+[a-z0-9]*)$#i', $value, $matches)) {
            throw new InvalidArgumentException("Invalid amount '$value'.");
        }

        return new self(new PhpMoney(
            $matches['amount'],
            new PhpCurrency(strtoupper($matches['currency']))
        ));
    }

    public function unwrap(): PhpMoney
    {
        return $this->money;
    }

    /** @param string|Currency $currency */
    public static function zero($currency): self
    {
        return self::fromNative('0'.(string)$currency);
    }

    public function toNative(): string
    {
        return $this->money->getAmount().$this->money->getCurrency()->getCode();
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    private function __construct(PhpMoney $money)
    {
        $this->money = $money;
    }
}
