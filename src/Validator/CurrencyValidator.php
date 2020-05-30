<?php declare(strict_types=1);
/**
 * This file is part of the daikon/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Validator;

use Assert\Assert;
use Daikon\Boot\Middleware\Action\ValidatorInterface;
use Daikon\Boot\Middleware\Action\ValidatorTrait;
use Daikon\Boot\Middleware\ActionHandler;
use Daikon\Money\ValueObject\Currency;

final class CurrencyValidator implements ValidatorInterface
{
    use ValidatorTrait;

    private string $input;

    /** @var null|bool|string */
    private $export;

    /** @var mixed */
    private $default;

    private bool $required;

    private array $options;

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
        array $options = ['SAT'],
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
        $this->options = $options;
        $this->severity = $severity;
        $this->payload = $payload;
        $this->exportErrors = $exportErrors;
        $this->exportErrorCode = $exportErrorCode;
        $this->exportErrorSeverity = $exportErrorSeverity;
    }

    /** @param mixed $input */
    private function validate(string $name, $input): Currency
    {
        Assert::that($input)
            ->string('Must be a string.')
            ->notBlank('Must not be blank.')
            //@todo assert case insensitive
            ->inArray($this->options, 'Not acceptable.');

        return Currency::fromNative($input);
    }
}
