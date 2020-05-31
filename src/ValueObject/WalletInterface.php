<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\ValueObject;

use Daikon\ValueObject\ValueObjectInterface;

interface WalletInterface extends ValueObjectInterface
{
    public function isEmpty(): bool;

    public function getBalance(string $currency): MoneyInterface;

    public function hasBalance(string $currency): bool;

    public function credit(MoneyInterface $amount): self;

    public function debit(MoneyInterface $amount): self;
}
