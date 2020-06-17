<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Service;

use Daikon\Money\ValueObject\MoneyInterface;

interface MoneyServiceInterface
{
    public function parse(string $amount, string $currency = null): MoneyInterface;

    public function format(MoneyInterface $money): string;

    public function convert(MoneyInterface $money, string $currency): MoneyInterface;
}
