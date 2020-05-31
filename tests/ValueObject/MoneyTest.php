<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/value-object project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace Daikon\Tests\Money\ValueObject;

use Daikon\Money\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testFromNative(): void
    {
        $this->assertEquals('100USD', Money::fromNative('100usd')->toNative());
        $this->assertEquals('-100A', Money::fromNative('-100A')->toNative());

        $this->expectException(InvalidArgumentException::class);
        Money::fromNative('100');
    } // @codeCoverageIgnore

    public function testToString(): void
    {
        $this->assertEquals('100USD1', (string)Money::fromNative('100uSd1'));
        $this->assertEquals('-100A', (string)Money::fromNative('-100A'));
    }

    public function testZero(): void
    {
        $this->assertEquals('0AB', Money::zero('Ab')->toNative());

        $this->expectException(InvalidArgumentException::class);
        Money::zero();
    } // @codeCoverageIgnore
}
