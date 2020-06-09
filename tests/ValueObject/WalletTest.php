<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace Daikon\Tests\Money\ValueObject;

use Daikon\Money\ValueObject\Money;
use Daikon\Money\ValueObject\Wallet;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WalletTest extends TestCase
{
    public function testMakeEmpty(): void
    {
        $wallet = Wallet::makeEmpty();
        $this->assertCount(0, $wallet->unwrap());
    }

    public function testIsEmpty(): void
    {
        $wallet = Wallet::makeEmpty();
        $this->assertTrue($wallet->isEmpty());

        $wallet = Wallet::makeEmpty()->credit(Money::fromNative('0SAT'));
        $this->assertTrue($wallet->isEmpty());

        $wallet = Wallet::makeEmpty()
            ->credit(Money::fromNative('0USD'))
            ->credit(Money::fromNative('1MSAT'));
        $this->assertFalse($wallet->isEmpty());
    }

    public function testCredit(): void
    {
        $wallet = Wallet::makeEmpty();
        $creditedWallet = $wallet->credit(Money::fromNative('100SAT'));
        $this->assertEquals(['SAT' => '100SAT'], $creditedWallet->unwrap());

        $wallet = Wallet::makeEmpty();
        $creditedWallet = $wallet->credit(Money::fromNative('-100SAT'));
        $this->assertEquals(['SAT' => '-100SAT'], $creditedWallet->unwrap());
    }

    public function testDebit(): void
    {
        $wallet = Wallet::makeEmpty();
        $debitedWallet = $wallet->debit(Money::fromNative('100SAT'));
        $this->assertEquals(['SAT' => '-100SAT'], $debitedWallet->unwrap());

        $wallet = Wallet::makeEmpty();
        $debitedWallet = $wallet->debit(Money::fromNative('-100SAT'));
        $this->assertEquals(['SAT' => '100SAT'], $debitedWallet->unwrap());
    }

    public function testGetBalance(): void
    {
        $wallet = Wallet::makeEmpty();
        $balance = $wallet->getBalance('X1');
        $this->assertInstanceOf(Money::class, $balance);
        $this->assertEquals(0, $balance->getAmount());
        $this->assertEquals('X1', $balance->getCurrency());

        $wallet = Wallet::fromNative(['SAT' => '-100SAT']);
        $balance = $wallet->getBalance('SAT');
        $this->assertEquals('-100SAT', (string)$balance);

        $wallet = Wallet::makeEmpty();
        $credit = Money::fromNative('100SAT');
        $creditedWallet = $wallet->credit($credit);
        $balance = $creditedWallet->getBalance('SAT');
        $this->assertNotSame($credit, $balance);
        $this->assertEquals($credit, $balance);

        //@todo assertion on lowercase currency key
        // $this->expectException(InvalidArgumentException::class);
        // Wallet::fromNative(['sat' => '100SAT']);
    }
}
