<?php declare(strict_types=1);
/**
 * This file is part of the daikon/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Service;

use Daikon\DataStructure\TypedMapInterface;
use Daikon\DataStructure\TypedMapTrait;

final class MoneyServiceMap implements TypedMapInterface
{
    use TypedMapTrait;

    public function __construct(iterable $services = [])
    {
        $this->init($services, [MoneyServiceInterface::class]);
    }
}
