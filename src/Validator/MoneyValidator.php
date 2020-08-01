<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Validator;

use Daikon\Interop\Assertion;
use Daikon\Interop\InvalidArgumentException;
use Daikon\Money\Service\MoneyService;
use Daikon\Money\ValueObject\MoneyInterface;
use Daikon\Validize\Validator\Validator;
use Money\Exception\ParserException;

final class MoneyValidator extends Validator
{
    private MoneyService $moneyService;

    public function __construct(MoneyService $moneyService)
    {
        $this->moneyService = $moneyService;
    }

    /** @param mixed $input */
    protected function validate($input): MoneyInterface
    {
        Assertion::string($input, 'Must be a string.');

        $settings = $this->getSettings();
        $convert = $settings['convert'] ?? false;
        $min = $settings['min'] ?? false;
        $max = $settings['max'] ?? false;

        try {
            $money = $this->moneyService->parse($input);
            if ($convert) {
                $money = $this->moneyService->convert($money, $convert);
            }
        } catch (ParserException $error) {
            throw new InvalidArgumentException('Invalid amount.');
        }

        if ($min !== false) {
            Assertion::true(
                $money->isGreaterThanOrEqual($this->moneyService->parse($min)),
                "Amount must be at least $min."
            );
        }

        if ($max !== false) {
            Assertion::true(
                $money->isLessThanOrEqual($this->moneyService->parse($max)),
                "Amount must be at most $max."
            );
        }

        return $money;
    }
}
