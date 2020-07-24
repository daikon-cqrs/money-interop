<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace Daikon\Tests\Money\Service;

use Daikon\Money\Service\PaymentServiceInterface;
use Daikon\Money\Service\PaymentServiceMap;
use Daikon\Money\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class PaymentServiceMapTest extends TestCase
{
    public function testConstruct(): void
    {
        $service = $this->createMock(PaymentServiceInterface::class);
        $unwrappedMap = (new PaymentServiceMap(['a' => $service]))->unwrap();
        $this->assertNotSame($service, $unwrappedMap['a']);
        $this->assertEquals($service, $unwrappedMap['a']);
    }

    public function testEnabledForRequest(): void
    {
        $unavailable = $this->createMock(PaymentServiceInterface::class);
        $unavailable->expects($this->once())->method('canRequest')->willReturn(false);
        $availableService = $this->createMock(PaymentServiceInterface::class);
        $availableService->expects($this->once())->method('canRequest')->willReturn(true);
        $filteredMap = (new PaymentServiceMap(['a' => $unavailable, 'b' => $availableService]))
            ->availableForRequest(Money::zero('X'));
        $unwrappedMap = $filteredMap->unwrap();
        $this->assertCount(1, $filteredMap);
        $this->assertNotSame($availableService, $unwrappedMap['b']);
        $this->assertEquals($availableService, $unwrappedMap['b']);
    }

    public function testEnabledForSend(): void
    {
        $unavailable = $this->createMock(PaymentServiceInterface::class);
        $unavailable->expects($this->once())->method('canSend')->willReturn(false);
        $availableService = $this->createMock(PaymentServiceInterface::class);
        $availableService->expects($this->once())->method('canSend')->willReturn(true);
        $filteredMap = (new PaymentServiceMap(['a' => $unavailable, 'b' => $availableService]))
            ->availableForSend(Money::zero('X'));
        $unwrappedMap = $filteredMap->unwrap();
        $this->assertCount(1, $filteredMap);
        $this->assertNotSame($availableService, $unwrappedMap['b']);
        $this->assertEquals($availableService, $unwrappedMap['b']);
    }
}
