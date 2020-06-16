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
use Daikon\Money\ValueObject\Money;
use Money\Converter;
use Money\Currencies;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\CurrencyList;
use Money\Exchange;
use Money\Exchange\FixedExchange;
use Money\Exchange\ReversedCurrenciesExchange;
use Money\Formatter\AggregateMoneyFormatter;
use Money\Parser\AggregateMoneyParser;

final class MoneyServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $settings = $serviceDefinition->getSettings();

        $factory = function (Injector $injector) use ($settings): object {
            $currencies = $this->buildCurrencies($injector, $settings['currencies'] ?? []);
            $parsers = $this->buildParsers($injector, $currencies, $settings['parsers'] ?? []);
            $formatters = $this->buildFormatters($injector, $settings['formatters'] ?? []);
            $exchanges = $this->buildExchanges($injector, $settings['exchanges'] ?? []);
            return new MoneyService(
                $parsers,
                new Converter($currencies, $exchanges),
                $formatters,
                $settings['type'] ?? Money::class
            );
        };

        $injector
            ->share(MoneyService::class)
            ->delegate(MoneyService::class, $factory);
    }

    private function buildCurrencies(Injector $injector, array $currencyConfigs): AggregateCurrencies
    {
        $currencies = [];
        foreach ($currencyConfigs as $currencyConfig) {
            //@todo support plain array of currencies
            $currencies[] = $injector->make($currencyConfig['class'], $currencyConfig['settings'] ?? []);
        }
        return new AggregateCurrencies($currencies);
    }

    private function buildParsers(
        Injector $injector,
        Currencies $currencies,
        array $parserConfigs = []
    ): AggregateMoneyParser {
        $parsers = [];
        foreach ($parserConfigs as $parserConfig) {
            $parsers[] = $injector->make($parserConfig['class'], [':currencies' => $currencies]);
        }
        return new AggregateMoneyParser($parsers);
    }

    private function buildFormatters(Injector $injector, array $formatterConfigs = []): AggregateMoneyFormatter
    {
        $formatters = [];
        foreach ($formatterConfigs as $formatterConfig) {
            if (is_array($formatterConfig['currencies'])) {
                $currencies = new CurrencyList($formatterConfig['currencies']);
            } else {
                $currencies = $injector->make($formatterConfig['currencies']);
            }
            foreach ($currencies as $currency) {
                $formatters[(string)$currency] = $injector->make(
                    $formatterConfig['class'],
                    [':currencies' => $currencies]
                );
            }
        }
        return new AggregateMoneyFormatter($formatters);
    }

    private function buildExchanges(Injector $injector, array $exchangeConfigs = []): Exchange
    {
        //@todo more exchange service support
        return new ReversedCurrenciesExchange(new FixedExchange($exchangeConfigs['fixed_rate'] ?? []));
    }
}
