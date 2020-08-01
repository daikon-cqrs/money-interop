<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Validator;

use Daikon\Interop\Assert;
use Daikon\Money\Service\PaymentServiceInterface;
use Daikon\Money\Service\PaymentServiceMap;
use Daikon\Validize\Validator\Validator;

final class PaymentServiceValidator extends Validator
{
    private PaymentServiceMap $paymentServiceMap;

    public function __construct(PaymentServiceMap $paymentServiceMap)
    {
        $this->paymentServiceMap = $paymentServiceMap;
    }

    /** @param mixed $input */
    protected function validate($input): PaymentServiceInterface
    {
        Assert::that($input)
            ->string('Must be a string.')
            ->notBlank('Must not be empty.')
            ->satisfy([$this->paymentServiceMap, 'has'], 'Unknown service.');

        /** @var PaymentServiceInterface $service  */
        $service = $this->paymentServiceMap->get($input);

        return $service;
    }
}
