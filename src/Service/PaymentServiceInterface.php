<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Service;

use Daikon\Money\ValueObject\MoneyInterface;

interface PaymentServiceInterface
{
    public function canRequest(MoneyInterface $amount): bool;

    public function canSend(MoneyInterface $amount): bool;
}
