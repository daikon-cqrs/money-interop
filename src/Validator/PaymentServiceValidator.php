<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Validator;

use Daikon\Boot\Middleware\Action\ValidatorInterface;
use Daikon\Boot\Middleware\Action\ValidatorTrait;
use Daikon\Boot\Middleware\ActionHandler;
use Daikon\Interop\Assert;
use Daikon\Money\Service\PaymentServiceInterface;
use Daikon\Money\Service\PaymentServiceMap;

final class PaymentServiceValidator implements ValidatorInterface
{
    use ValidatorTrait;

    private PaymentServiceMap $paymentServiceMap;

    private string $input;

    /** @var null|bool|string */
    private $export;

    /** @var mixed */
    private $default;

    private bool $required;

    private int $severity;

    private string $payload;

    private string $exportErrors;

    private string $exportErrorCode;

    private string $exportErrorSeverity;

    /**
     * @param mixed $export
     * @param mixed $default
     */
    public function __construct(
        PaymentServiceMap $paymentServiceMap,
        string $input,
        $export = null,
        $default = null,
        bool $required = true,
        int $severity = self::SEVERITY_ERROR,
        string $payload = ActionHandler::ATTR_PAYLOAD,
        string $exportErrors = ActionHandler::ATTR_ERRORS,
        string $exportErrorCode = ActionHandler::ATTR_STATUS_CODE,
        string $exportErrorSeverity = ActionHandler::ATTR_ERROR_SEVERITY
    ) {
        $this->paymentServiceMap = $paymentServiceMap;
        $this->input = $input;
        $this->export = $export;
        $this->default = $default;
        $this->required = $required;
        $this->severity = $severity;
        $this->payload = $payload;
        $this->exportErrors = $exportErrors;
        $this->exportErrorCode = $exportErrorCode;
        $this->exportErrorSeverity = $exportErrorSeverity;
    }

    /** @param mixed $input */
    private function validate(string $name, $input): PaymentServiceInterface
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
