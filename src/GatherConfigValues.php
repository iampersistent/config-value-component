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

        $configuredValues = $container->get('config')[$configName] ?? [];
        $values = $this->configMerge($values, $configuredValues);
        $envValues = $this->extractEnvValues($configName);

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

    private function extractEnvValues(string $configName): array
    {
        $env = $_SERVER;
        $envValues = [];
        $prefix = strtoupper($configName) . '_';
        $prefixLength = strlen($prefix);
        foreach ($env as $key => $value) {
            if (0 === strpos($key, $prefix)) {
                $valueArray = [];
                $envKey = strtolower(substr($key, $prefixLength));
                $parts = array_reverse(explode('_', $envKey));
                $innerKey = array_shift($parts);
                $valueArray[$innerKey] = $value;
                foreach ($parts as $partKey) {
                    $valueArray = [
                        $partKey => $valueArray,
                    ] ;
                }
                $envValues = array_merge($envValues, $valueArray);
            }
        }

        return $envValues;
    }

    private function envMerge(array $startingArray, array $envArray): array
    {
        foreach ($startingArray as $key => $value) {
            $envKey = strtolower($key);
            if (!isset($envArray[$envKey]) && !isset($envArray[$key])) {
                continue;
            }
            $envValue = $envArray[$envKey] ?? $envArray[$key];

            if (is_array($value)) {
                $startingArray[$key] = $this->envMerge($startingArray[$key], $envValue);
            } else {
                $startingArray[$key] = $envValue;
            }
            unset($envArray[$envKey], $envArray[$key]);
        }
        foreach ($envArray as $key => $envValue) {
            if (is_array($envValue) && isset($startingArray[$key])) {
                $startingArray[$key] = $this->envMerge($startingArray[$key], $envValue);
            } else {
                $startingArray[$key] = $envValue;
            }
        }

        return $startingArray;
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
