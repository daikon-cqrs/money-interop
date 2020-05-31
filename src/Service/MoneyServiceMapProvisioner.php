<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/money-interop project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Money\Service;

use Auryn\Injector;
use Daikon\Boot\Service\Provisioner\ProvisionerInterface;
use Daikon\Boot\Service\ServiceDefinitionInterface;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;

final class MoneyServiceMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceConfigs = $configProvider->get('payments', []);
        $factory = function (ConnectorMap $connectorMap) use ($injector, $serviceConfigs): MoneyServiceMap {
            $services = [];
            foreach ($serviceConfigs as $serviceName => $serviceConfig) {
                $serviceClass = $serviceConfig['class'];
                $services[$serviceName] = $injector->define(
                    $serviceClass,
                    [
                        ':connector' => $connectorMap->get($serviceConfig['connector']),
                        ':settings' => $serviceConfig['settings'] ?? []
                    ]
                )->make($serviceClass);
            }
            return new MoneyServiceMap($services);
        };

        $injector
            ->share(MoneyServiceMap::class)
            ->delegate(MoneyServiceMap::class, $factory);
    }
}
