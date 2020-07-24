<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Service;

use Daikon\DataStructure\TypedMap;
use Daikon\Money\ValueObject\MoneyInterface;

final class PaymentServiceMap extends TypedMap
{
    public function __construct(iterable $services = [])
    {
        $this->init($services, [PaymentServiceInterface::class]);
    }

    public function availableForRequest(MoneyInterface $amount): self
    {
        return $this->filter(
            fn(string $key, PaymentServiceInterface $paymentService): bool => $paymentService->canRequest($amount)
        );
    }

    public function availableForSend(MoneyInterface $amount): self
    {
        return $this->filter(
            fn(string $key, PaymentServiceInterface $paymentService): bool => $paymentService->canSend($amount)
        );
    }
}
