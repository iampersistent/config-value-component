<?php
declare(strict_types=1);

namespace ConfigValue;

use GetOffMyCase\CamelCase;
use GetOffMyCase\SnakeCase;
use Psr\Container\ContainerInterface;

final class GatherConfigValues
{
    public function __invoke(ContainerInterface $container, string $configName, array $default = []): array
    {
        $values = $this->getDefaultValues($default);

        $configedValues = $container->get('config')[$configName] ?? [];
        $values = $this->configMerge($values, $configedValues);

        $env = $_SERVER;
        $envValues = [];
        $prefix = strtoupper($configName) . '_';
        $prefixLength = strlen($prefix);
        foreach ($env as $key => $value) {
            if (0 === strpos($key, $prefix)) {
                $envKey = strtolower(substr($key, $prefixLength));
                $envValues[$envKey] = $value;
            }
        }

        return $this->envMerge($values, $envValues);
    }

    private function configMerge(array $configValues, array $mergingConfig): array
    {
        foreach ($mergingConfig as $key => $value) {
            if (is_array($value)) {
                $configValues[$key] = $this->configMerge($configValues[$key] ?? [], $value);

                continue;
            }
            $configValues[$key] = $value;
        }

        return $configValues;
    }

    private function envMerge(array $startingConfig, array $envValues): array
    {
        foreach ($startingConfig as $key => $value) {
            $envKey = (new SnakeCase)($key);
            if (isset($envValues[$envKey])) {
                $startingConfig[$key] = $envValues[$envKey];
                unset($envValues[$key]);

                continue;
            }
            if (is_array($value)) {
                $subEnvValues = [];
                $prefix = $key . '_';
                $prefixLength = strlen($prefix);
                foreach ($envValues as $envValueKey => $envValue) {
                    if (0 === strpos($envValueKey, $prefix)) {
                        $envKey = substr($envValueKey, $prefixLength);
                        $subEnvValues[$envKey] = $envValue;
                        unset($envValues[$envValueKey]);
                    }
                }
                if (!empty($subEnvValues)) {
                    $startingConfig[$key] = $this->envMerge($startingConfig[$key], $subEnvValues);
                }
            }
        }
        foreach ($envValues as $key => $value) {
            $startingConfig[(new CamelCase)($key)] = $value;
        }

        return $startingConfig;
    }

    private function getDefaultValues(array $defaults): array
    {
        $returnValues = [];
        foreach ($defaults as $key => $value) {
            if (is_array($value)) {
                $defaultsChildren = $this->getDefaultValues($defaults[$key]);
                if (!empty($defaultsChildren)) {
                    $returnValues[$key] = $defaultsChildren;
                }
            } elseif (null !== $value) {
                $returnValues[$key] = $value;
            }
        }

        return $returnValues;
    }
}
