<?php declare(strict_types=1);
/**
 * This file is part of the oroshi/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oroshi\Money\Validator;

use Assert\Assert;
use Oroshi\Core\Middleware\ActionHandler;
use Oroshi\Core\Middleware\Action\ValidatorInterface;
use Oroshi\Core\Middleware\Action\ValidatorTrait;
use Oroshi\Money\ValueObject\Money;

final class MoneyValidator implements ValidatorInterface
{
    use ValidatorTrait;

    private const MIN_VAL = PHP_INT_MIN;
    private const MAX_VAL = PHP_INT_MAX;

    private string $input;

    /** @var null|bool|string */
    private $export;

    /** @var mixed */
    private $default;

    private bool $required;

    private int $min;

    private int $max;

    private array $currencies;

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
        string $input,
        $export = null,
        $default = null,
        bool $required = true,
        int $min = self::MIN_VAL,
        int $max = self::MAX_VAL,
        array $currencies = ['SAT'],
        int $severity = self::SEVERITY_ERROR,
        string $payload = ActionHandler::ATTR_PAYLOAD,
        string $exportErrors = ActionHandler::ATTR_ERRORS,
        string $exportErrorCode = ActionHandler::ATTR_ERROR_CODE,
        string $exportErrorSeverity = ActionHandler::ATTR_ERROR_SEVERITY
    ) {
        $this->input = $input;
        $this->export = $export;
        $this->default = $default;
        $this->required = $required;
        $this->min = $min;
        $this->max = $max;
        $this->currencies = $currencies;
        $this->severity = $severity;
        $this->payload = $payload;
        $this->exportErrors = $exportErrors;
        $this->exportErrorCode = $exportErrorCode;
        $this->exportErrorSeverity = $exportErrorSeverity;
    }

    /** @param mixed $input */
    private function validate(string $name, $input): Money
    {
        Assert::that($input)
            ->isArray('Must be an an array.')
            ->keyExists('amount', 'Amount must be specified.')
            ->keyExists('currency', 'Currency must be specified.');
        Assert::lazy()
            ->that($input['amount'], $name)
            ->integerish('Amount must be an integer.')
            ->greaterOrEqualThan($this->min, "Amount must be at least $this->min.")
            ->lessOrEqualThan($this->max, "Amount must be at most $this->max.")
            ->that($input['currency'], $name)
            ->string('Currency must be a string.')
            ->notBlank('Currency must not be empty.')
            ->inArray($this->currencies, 'Currency not valid.')
            ->verifyNow();

        return Money::fromNative($input);
    }
}
