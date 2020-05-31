<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\ValueObject;

use Daikon\ValueObject\ValueObjectInterface;
use Money\Money;

interface MoneyInterface extends ValueObjectInterface
{
    public const ROUND_UP = Money::ROUND_UP;
    public const ROUND_DOWN = Money::ROUND_DOWN;
    public const ROUND_HALF_UP = Money::ROUND_HALF_UP;
    public const ROUND_HALF_DOWN = Money::ROUND_HALF_DOWN;

    /** @param null|string $currency */
    public static function zero($currency = null): self;

    public function getAmount(): string;

    public function getCurrency(): string;

    public function add(self $money): self;

    public function subtract(self $money): self;

    /** @param float|int|string $multiplier */
    public function multiply($multiplier, int $roundingMode = self::ROUND_HALF_UP): self;

    /** @param float|int|string $divisor */
    public function divide($divisor, int $roundingMode = self::ROUND_HALF_UP): self;

    public function isZero(): bool;

    public function isPositive(): bool;

    public function isNegative(): bool;

    public function isLessThanOrEqual(self $money): bool;

    public function isGreaterThanOrEqual(self $money): bool;
}