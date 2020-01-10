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
    private PhpCurrency $value;

    /** @param self $comparator */
    public function equals($comparator): bool
    {
        Assertion::isInstanceOf($comparator, self::class);
        return $this->toNative() === $comparator->toNative();
    }

    public function getCurrency(): string
    {
        return $this->value->getCode();
    }

    /** @param string $value */
    public static function fromNative($value): self
    {
        Assertion::string($value);
        return new self(new PhpCurrency($value));
    }

    public function toNative(): string
    {
        return $this->value->getCode();
    }

    public function __toString(): string
    {
        return $this->value->getCode();
    }

    private function __construct(PhpCurrency $value)
    {
        $this->value = $value;
    }
}
