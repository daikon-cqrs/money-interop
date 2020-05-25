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

final class Currency implements ValueObjectInterface
{
    private PhpCurrency $currency;

    /** @param self $comparator */
    public function equals($comparator): bool
    {
        Assertion::isInstanceOf($comparator, self::class);
        return $this->toNative() === $comparator->toNative();
    }

    public function getCode(): string
    {
        return $this->currency->getCode();
    }

    /** @param string $value */
    public static function fromNative($value): self
    {
        Assertion::string($value, 'Must be a string.');
        Assertion::regex($value, '#^[a-z]+[a-z0-9]*$#i', "Invalid currency '$value'.");

        return new self(new PhpCurrency(strtoupper($value)));
    }

    public function toNative(): string
    {
        return $this->currency->getCode();
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    private function __construct(PhpCurrency $currency)
    {
        $this->currency = $currency;
    }
}
