<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace Daikon\Tests\Money\ValueObject;

use Daikon\Interop\InvalidArgumentException;
use Daikon\Money\ValueObject\Money;
use Daikon\Money\ValueObject\MoneyInterface;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testFromNative(): void
    {
        $this->assertEquals('100USD', Money::fromNative('100USD')->toNative());
        $this->assertEquals('-100A', Money::fromNative('-100A')->toNative());

        $this->expectException(InvalidArgumentException::class);
        Money::fromNative('100');
    }

    public function testToString(): void
    {
        $this->assertEquals('100USD1', (string)Money::fromNative('100USD1'));
        $this->assertEquals('-100A', (string)Money::fromNative('-100A'));
    }

    public function testZero(): void
    {
        $this->assertEquals('0AB', Money::zero('AB')->toNative());

        $this->expectException(InvalidArgumentException::class);
        Money::zero();
    }

    public function testEquals(): void
    {
        $money = Money::fromNative('0SAT');
        $this->assertTrue($money->equals(Money::fromNative('0SAT')));
        $this->assertFalse($money->equals(Money::fromNative('0MSAT')));
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        $this->assertFalse($money->equals('nan'));
    }

    public function testGetAmount(): void
    {
        $money = Money::fromNative('0SAT');
        $this->assertSame('0', $money->getAmount());
    }

    public function testGetCurrency(): void
    {
        $money = Money::fromNative('0SAT');
        $this->assertSame('SAT', $money->getCurrency());
    }

    public function testPercentage(): void
    {
        $money = Money::fromNative('0SAT');
        $this->assertEquals('0SAT', (string)$money->percentage(0));
        $this->assertSame('0SAT', (string)$money->percentage(10));
        $this->assertSame('0', $money->percentage(10.12345, MoneyInterface::ROUND_UP)->getAmount());
        $this->assertEquals('0SAT', (string)$money->percentage(100, MoneyInterface::ROUND_DOWN));

        $money = Money::fromNative('100SAT');
        $this->assertEquals('0SAT', (string)$money->percentage(0));
        $this->assertSame('10SAT', (string)$money->percentage(10));
        $this->assertSame('11', $money->percentage(10.12345, MoneyInterface::ROUND_UP)->getAmount());
        $this->assertSame('10', $money->percentage(10.12345, MoneyInterface::ROUND_DOWN)->getAmount());
        $this->assertEquals('100SAT', (string)$money->percentage(100, MoneyInterface::ROUND_DOWN));
    }
}
