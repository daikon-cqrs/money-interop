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
        $disabledService = $this->createMock(PaymentServiceInterface::class);
        $disabledService->expects($this->once())->method('canRequest')->willReturn(false);
        $enabledService = $this->createMock(PaymentServiceInterface::class);
        $enabledService->expects($this->once())->method('canRequest')->willReturn(true);
        $filteredMap = (new PaymentServiceMap(['a' => $disabledService, 'b' => $enabledService]))->enabledForRequest();
        $unwrappedMap = $filteredMap->unwrap();
        $this->assertCount(1, $filteredMap);
        $this->assertNotSame($enabledService, $unwrappedMap['b']);
        $this->assertEquals($enabledService, $unwrappedMap['b']);
    }

    public function testEnabledForSend(): void
    {
        $disabledService = $this->createMock(PaymentServiceInterface::class);
        $disabledService->expects($this->once())->method('canSend')->willReturn(false);
        $enabledService = $this->createMock(PaymentServiceInterface::class);
        $enabledService->expects($this->once())->method('canSend')->willReturn(true);
        $filteredMap = (new PaymentServiceMap(['a' => $disabledService, 'b' => $enabledService]))->enabledForSend();
        $unwrappedMap = $filteredMap->unwrap();
        $this->assertCount(1, $filteredMap);
        $this->assertNotSame($enabledService, $unwrappedMap['b']);
        $this->assertEquals($enabledService, $unwrappedMap['b']);
    }
}
