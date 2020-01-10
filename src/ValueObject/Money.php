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
use Money\Currency as PhpCurrency;
use Money\Money as PhpMoney;

final class Money implements ValueObjectInterface
{
    public const ROUND_UP = PhpMoney::ROUND_UP;
    public const ROUND_DOWN = PhpMoney::ROUND_DOWN;
    public const ROUND_HALF_UP = PhpMoney::ROUND_HALF_UP;
    public const ROUND_HALF_DOWN = PhpMoney::ROUND_HALF_DOWN;

    private PhpMoney $value;

    /** @param self $comparator */
    public function equals($comparator): bool
    {
        Assertion::isInstanceOf($comparator, self::class);
        return $this->toNative() === $comparator->toNative();
    }

    public function getAmount(): string
    {
        return $this->value->getAmount();
    }

    public function getCurrency(): Currency
    {
        return Currency::fromNative((string)$this->value->getCurrency());
    }

    /** @param float|int|string $multiplier */
    public function multiply($multiplier, int $roundingMode = self::ROUND_HALF_UP): self
    {
        Assertion::numeric($multiplier, 'Multipler must be numeric.');
        $multiplied = $this->value->multiply($multiplier, $roundingMode);
        return new self($multiplied);
    }

    /** @param float|int|string $divisor */
    public function divide($divisor, int $roundingMode = self::ROUND_HALF_UP): self
    {
        Assertion::numeric($divisor, 'Divider must be numeric.');
        $divided = $this->value->divide($divisor, $roundingMode);
        return new self($divided);
    }

    public function add(Money $money): self
    {
        $added = $this->value->add($money->toBaseMoney());
        return new self($added);
    }

    public function subtract(Money $money): self
    {
        $subtracted = $this->value->subtract($money->toBaseMoney());
        return new self($subtracted);
    }

    public function isZero(): bool
    {
        return $this->value->isZero();
    }

    public function isPositive(): bool
    {
        return $this->value->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->value->isNegative();
    }

    public function isLessThanOrEqual(Money $money): bool
    {
        return $this->value->lessThanOrEqual($money->toBaseMoney());
    }

    public function isGreaterThanOrEqual(Money $money): bool
    {
        return $this->value->greaterThanOrEqual($money->toBaseMoney());
    }

    /** @param array $value */
    public static function fromNative($value): self
    {
        Assertion::isArray($value, 'Trying to create Money VO from unsupported value type.');
        Assertion::keyExists($value, 'amount');
        Assertion::keyExists($value, 'currency');

        return new self(new PhpMoney((string)$value['amount'], new PhpCurrency((string)$value['currency'])));
    }

    public function toNative(): array
    {
        return [
            'amount' => (string)$this->value->getAmount(),
            'currency' => (string)$this->value->getCurrency()
        ];
    }

    public function toBaseMoney(): PhpMoney
    {
        return $this->value;
    }

    public static function zero(Currency $currency): self
    {
        return new self(new PhpMoney(0, new PhpCurrency((string)$currency)));
    }

    public function __toString(): string
    {
        return $this->value->getAmount().' '.$this->value->getCurrency();
    }

    private function __construct(PhpMoney $value)
    {
        $this->value = $value;
    }
}
