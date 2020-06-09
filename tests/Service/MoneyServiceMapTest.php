<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace Daikon\Tests\Money\Service;

use Daikon\Money\Service\MoneyServiceInterface;
use Daikon\Money\Service\MoneyServiceMap;
use PHPUnit\Framework\TestCase;

final class MoneyServiceMapTest extends TestCase
{
    public function testConstruct(): void
    {
        $service = $this->createMock(MoneyServiceInterface::class);
        $unwrappedMap = (new MoneyServiceMap(['a' => $service]))->unwrap();
        $this->assertNotSame($service, $unwrappedMap['a']);
        $this->assertEquals($service, $unwrappedMap['a']);
    }
}
