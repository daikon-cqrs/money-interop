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
    protected ?PhpMoney $money;

    /** @param static $comparator */
    public function equals($comparator): bool
    {
        Assertion::isInstanceOf($comparator, static::class);
        return $this->toNative() === $comparator->toNative();
    }

    public function getAmount(): string
    {
        $this->assertNotEmpty();
        return $this->money->getAmount();
    }

    public function getCurrency(): string
    {
        $this->assertNotEmpty();
        return $this->money->getCurrency()->getCode();
    }

    /** @return static */
    public function multiply($multiplier, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $this->assertNotEmpty();
        Assertion::numeric($multiplier, 'Multipler must be numeric.');
        $multiplied = $this->money->multiply($multiplier, $roundingMode);
        return new static($multiplied);
    }

    /** @return static */
    public function divide($divisor, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $this->assertNotEmpty();
        Assertion::numeric($divisor, 'Divider must be numeric.');
        Assertion::notEq(0, $divisor, 'Divisor must not be zero.');
        $divided = $this->money->divide($divisor, $roundingMode);
        return new static($divided);
    }

    /** @return static */
    public function percentage($percentage, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $this->assertNotEmpty();
        return $this->multiply($percentage)->divide(100, $roundingMode);
    }

    /** @return static */
    public function add(MoneyInterface $money): self
    {
        $this->assertNotEmpty();
        $this->assertSameCurrency($money);
        Assertion::false($money->isEmpty(), 'Addition must not be empty.');
        $added = $this->money->add(
            static::asBaseMoney($money->getAmount(), $money->getCurrency())
        );
        return new static($added);
    }

    /** @return static */
    public function subtract(MoneyInterface $money): self
    {
        $this->assertNotEmpty();
        $this->assertSameCurrency($money);
        Assertion::false($money->isEmpty(), 'Subtraction must not be empty.');
        $subtracted = $this->money->subtract(
            static::asBaseMoney($money->getAmount(), $money->getCurrency())
        );
        return new static($subtracted);
    }

    /** @return static */
    public static function makeEmpty(): self
    {
        return new static;
    }

    public function isEmpty(): bool
    {
        return $this->money === null;
    }

    public function isZero(): bool
    {
        $this->assertNotEmpty();
        return $this->money->isZero();
    }

    public function isPositive(): bool
    {
        $this->assertNotEmpty();
        return $this->money->isPositive();
    }

    public function isNegative(): bool
    {
        $this->assertNotEmpty();
        return $this->money->isNegative();
    }

    public function isLessThanOrEqual(MoneyInterface $comparator): bool
    {
        $this->assertNotEmpty();
        $this->assertSameCurrency($comparator);
        Assertion::false($comparator->isEmpty(), 'Comparator must not be empty.');
        return $this->money->lessThanOrEqual(
            static::asBaseMoney($comparator->getAmount(), $comparator->getCurrency())
        );
    }

    public function isGreaterThanOrEqual(MoneyInterface $comparator): bool
    {
        $this->assertNotEmpty();
        $this->assertSameCurrency($comparator);
        Assertion::false($comparator->isEmpty(), 'Comparator must not be empty.');
        return $this->money->greaterThanOrEqual(
            static::asBaseMoney($comparator->getAmount(), $comparator->getCurrency())
        );
    }

    /**
     * @param null|string $value
     * @return static
     */
    public static function fromNative($value): self
    {
        Assertion::nullOrString($value, 'Must be a string.');
        if ($value === null) {
            return new static;
        }

        if (!preg_match('/^(?<amount>-?\d+)\s?(?<currency>[a-z][a-z0-9]*)$/i', $value, $matches)) {
            throw new InvalidArgumentException('Invalid amount.');
        }

        return new static(static::asBaseMoney($matches['amount'], $matches['currency']));
    }

    /** @return static */
    public static function zero($currency = null): self
    {
        Assertion::regex($currency, '/^[a-z][a-z0-9]*$/i', 'Invalid currency.');
        return static::fromNative('0'.$currency);
    }

    public function toNative(): ?string
    {
        return !$this->isEmpty() ? $this->getAmount().$this->getCurrency() : null;
    }

    public function __toString(): string
    {
        return (string)$this->toNative();
    }

    protected static function asBaseMoney(string $amount, string $currency): PhpMoney
    {
        return new PhpMoney($amount, new PhpCurrency($currency));
    }

    protected function assertNotEmpty(): void
    {
        Assertion::false($this->isEmpty(), 'Money is empty.');
    }

    protected function assertSameCurrency(MoneyInterface $money): void
    {
        Assertion::eq($this->getCurrency(), $money->getCurrency(), 'Currencies must be identical.');
    }

    final protected function __construct(?PhpMoney $money = null)
    {
        $this->money = $money;
    }
}
