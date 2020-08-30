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
        $this->assertNull(Money::fromNative(null)->toNative());

        $this->expectException(InvalidArgumentException::class);
        Money::fromNative('100');
    }

    public function testToString(): void
    {
        $this->assertEquals('100USD1', (string)Money::fromNative('100USD1'));
        $this->assertEquals('-100A', (string)Money::fromNative('-100A'));
        $this->assertEquals('', Money::fromNative(null));
        $this->assertEquals('', Money::makeEmpty());
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

        $this->assertTrue(Money::makeEmpty()->equals(Money::fromNative(null)));

        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        $money->equals('nan');
    }

    public function testGetAmount(): void
    {
        $money = Money::fromNative('0SAT');
        $this->assertSame('0', $money->getAmount());

        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->getAmount();
    }

    public function testGetCurrency(): void
    {
        $money = Money::fromNative('0SAT');
        $this->assertSame('SAT', $money->getCurrency());

        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->getCurrency();
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

    public function testPercentageOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->percentage(10);
    }

    public function testValueAsserts(): void
    {
        $money = Money::zero('SAT');
        $this->assertTrue($money->isZero());
        $this->assertFalse($money->isPositive());
        $this->assertFalse($money->isNegative());

        $money = Money::fromNative('1SAT');
        $this->assertFalse($money->isZero());
        $this->assertTrue($money->isPositive());
        $this->assertFalse($money->isNegative());

        $money = Money::fromNative('-1SAT');
        $this->assertFalse($money->isZero());
        $this->assertFalse($money->isPositive());
        $this->assertTrue($money->isNegative());
    }

    public function testIsZeroOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->isZero();
    }

    public function testIsPositiveOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->isPositive();
    }

    public function testIsNegativeOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->isNegative();
    }

    public function testIsLessThanOrEqual(): void
    {
        $zero = Money::zero('SAT');
        $pos = Money::fromNative('1SAT');
        $neg = Money::fromNative('-1SAT');
        $this->assertTrue($zero->isLessThanOrEqual($pos));
        $this->assertFalse($zero->isLessThanOrEqual($neg));
        $this->assertTrue($zero->isLessThanOrEqual($zero));
        $this->assertTrue($neg->isLessThanOrEqual($zero));
        $this->assertTrue($neg->isLessThanOrEqual($pos));
        $this->assertTrue($neg->isLessThanOrEqual($neg));
        $this->assertFalse($pos->isLessThanOrEqual($zero));
        $this->assertFalse($pos->isLessThanOrEqual($neg));
        $this->assertTrue($pos->isLessThanOrEqual($pos));
    }

    public function testIsLessThanOrEqualOnEmpty(): void
    {
        $money = Money::fromNative('1BTC');
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->isLessThanOrEqual($money);
    }

    public function testIsLessThanOrEqualWithEmpty(): void
    {
        $money = Money::fromNative('1BTC');
        $this->expectException(InvalidArgumentException::class);
        $money->isLessThanOrEqual(Money::makeEmpty());
    }

    public function testIsLessThanOrEqualWithDifferentCurrency(): void
    {
        $money = Money::fromNative('1BTC');
        $this->expectException(InvalidArgumentException::class);
        Money::zero('USD')->isLessThanOrEqual($money);
    }

    public function testIsGreaterThanOrEqual(): void
    {
        $zero = Money::zero('SAT');
        $pos = Money::fromNative('1SAT');
        $neg = Money::fromNative('-1SAT');
        $this->assertFalse($zero->isGreaterThanOrEqual($pos));
        $this->assertTrue($zero->isGreaterThanOrEqual($neg));
        $this->assertTrue($zero->isGreaterThanOrEqual($zero));
        $this->assertFalse($neg->isGreaterThanOrEqual($zero));
        $this->assertFalse($neg->isGreaterThanOrEqual($pos));
        $this->assertTrue($neg->isGreaterThanOrEqual($neg));
        $this->assertTrue($pos->isGreaterThanOrEqual($zero));
        $this->assertTrue($pos->isGreaterThanOrEqual($neg));
        $this->assertTrue($pos->isGreaterThanOrEqual($pos));
    }

    public function testIsGreaterThanOrEqualOnEmpty(): void
    {
        $money = Money::fromNative('1BTC');
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->isGreaterThanOrEqual($money);
    }

    public function testIsGreaterThanOrEqualWithEmpty(): void
    {
        $money = Money::fromNative('1BTC');
        $this->expectException(InvalidArgumentException::class);
        $money->isGreaterThanOrEqual(Money::makeEmpty());
    }

    public function testIsGreaterThanOrEqualWithDifferentCurrency(): void
    {
        $money = Money::fromNative('1BTC');
        $this->expectException(InvalidArgumentException::class);
        Money::zero('USD')->isGreaterThanOrEqual($money);
    }

    public function testMakeEmpty(): void
    {
        $money = Money::makeEmpty();
        $this->assertTrue($money->isEmpty());
        $this->assertNull($money->toNative());
    }

    public function testAdd(): void
    {
        $zero = Money::zero('USD');
        $pos = Money::fromNative('1USD');
        $neg = Money::fromNative('-1USD');
        $this->assertEquals('1USD', (string)$zero->add($pos));
        $this->assertEquals('-1USD', (string)$zero->add($neg));
        $this->assertEquals('1USD', (string)$pos->add($zero));
        $this->assertEquals('0USD', (string)$pos->add($neg));
        $this->assertEquals('-1USD', (string)$neg->add($zero));
        $this->assertEquals('0USD', (string)$neg->add($pos));
    }

    public function testAddOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->add(Money::fromNative('1GBP'));
    }

    public function testAddWithDifferentCurrency(): void
    {
        $money = Money::fromNative('1SAT');
        $this->expectException(InvalidArgumentException::class);
        $money->add(Money::fromNative('1GBP'));
    }

    public function testSubtract(): void
    {
        $zero = Money::zero('USD');
        $pos = Money::fromNative('1USD');
        $neg = Money::fromNative('-1USD');
        $this->assertEquals('-1USD', (string)$zero->subtract($pos));
        $this->assertEquals('1USD', (string)$zero->subtract($neg));
        $this->assertEquals('1USD', (string)$pos->subtract($zero));
        $this->assertEquals('2USD', (string)$pos->subtract($neg));
        $this->assertEquals('-1USD', (string)$neg->subtract($zero));
        $this->assertEquals('-2USD', (string)$neg->subtract($pos));
    }

    public function testSubtractOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->subtract(Money::fromNative('1GBP'));
    }

    public function testSubtractWithDifferentCurrency(): void
    {
        $money = Money::fromNative('1SAT');
        $this->expectException(InvalidArgumentException::class);
        $money->subtract(Money::fromNative('1GBP'));
    }

    public function testMultiply(): void
    {
        $zero = Money::zero('USD');
        $pos = Money::fromNative('12USD');
        $neg = Money::fromNative('-12USD');

        $this->assertTrue($zero->multiply(0)->isZero());
        $this->assertTrue($zero->multiply(2)->isZero());
        $this->assertTrue($zero->multiply(0.5)->isZero());
        $this->assertTrue($zero->multiply(-0.2)->isZero());

        $this->assertTrue($pos->multiply(0)->isZero());
        $this->assertEquals('24USD', (string)$pos->multiply(2));
        $this->assertEquals('6USD', (string)$pos->multiply(0.5));
        $this->assertEquals('-3USD', (string)$pos->multiply(-0.25));

        $this->assertTrue($neg->multiply(0)->isZero());
        $this->assertEquals('-24USD', (string)$neg->multiply(2));
        $this->assertEquals('-6USD', (string)$neg->multiply(0.5));
        $this->assertEquals('3USD', (string)$neg->multiply(-0.25));
    }

    public function testMultiplyOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->multiply(2);
    }

    public function testMultiplyNonNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->multiply('x');
    }

    public function testDivide(): void
    {
        $zero = Money::zero('USD');
        $pos = Money::fromNative('12USD');
        $neg = Money::fromNative('-12USD');

        $this->assertTrue($zero->divide(2)->isZero());
        $this->assertTrue($zero->divide(0.5)->isZero());
        $this->assertTrue($zero->divide(-0.2)->isZero());

        $this->assertEquals('6USD', (string)$pos->divide(2));
        $this->assertEquals('24USD', (string)$pos->divide(0.5));
        $this->assertEquals('-48USD', (string)$pos->divide(-0.25));

        $this->assertEquals('-6USD', (string)$neg->divide(2));
        $this->assertEquals('-24USD', (string)$neg->divide(0.5));
        $this->assertEquals('48USD', (string)$neg->divide(-0.25));

        $this->expectException(InvalidArgumentException::class);
        $pos->divide(0);
    }

    public function testDivideOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->divide(2);
    }

    public function testDivideNonNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::makeEmpty()->divide('x');
    }
}
