<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Validator;

use Daikon\Boot\Middleware\ActionHandler;
use Daikon\Boot\Middleware\Action\ValidatorInterface;
use Daikon\Boot\Middleware\Action\ValidatorTrait;
use Daikon\Interop\Assertion;
use Daikon\Interop\InvalidArgumentException;
use Daikon\Money\Service\MoneyService;
use Daikon\Money\ValueObject\MoneyInterface;
use Money\Exception\ParserException;

final class MoneyValidator implements ValidatorInterface
{
    use ValidatorTrait;

    private MoneyService $moneyService;

    private string $input;

    /** @var null|bool|string */
    private $export;

    /** @var mixed */
    private $default;

    private bool $required;
    
    private ?string $min;
    
    private ?string $max;

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
        MoneyService $moneyService,
        string $input,
        $export = null,
        $default = null,
        bool $required = true,
        string $min = null,
        string $max = null,
        int $severity = self::SEVERITY_ERROR,
        string $payload = ActionHandler::ATTR_PAYLOAD,
        string $exportErrors = ActionHandler::ATTR_ERRORS,
        string $exportErrorCode = ActionHandler::ATTR_ERROR_CODE,
        string $exportErrorSeverity = ActionHandler::ATTR_ERROR_SEVERITY
    ) {
        $this->moneyService = $moneyService;
        $this->input = $input;
        $this->export = $export;
        $this->default = $default;
        $this->required = $required;
        $this->min = $min;
        $this->max = $max;
        $this->severity = $severity;
        $this->payload = $payload;
        $this->exportErrors = $exportErrors;
        $this->exportErrorCode = $exportErrorCode;
        $this->exportErrorSeverity = $exportErrorSeverity;
    }

    /** @param mixed $input */
    private function validate(string $name, $input): MoneyInterface
    {
        Assertion::string($input, 'Must be a string.');

        try {
            $money = $this->moneyService->parse($input);
        } catch (ParserException $error) {
            throw new InvalidArgumentException('Invalid amount.');
        }

        if ($this->min && !$money->isGreaterThanOrEqual($this->moneyService->parse($this->min))) {
            throw new InvalidArgumentException("Amount must be at least $this->min.");
        }

        if ($this->max && !$money->isLessThanOrEqual($this->moneyService->parse($this->max))) {
            throw new InvalidArgumentException("Amount must be at most $this->max.");
        }

        return $money;
    }
}
