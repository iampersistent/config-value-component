<?php
declare(strict_types=1);

namespace Tests\Unit;

use ConfigValue\GatherConfigValues;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use UnitTester;

class GatherConfigValuesCest
{
    public function __construct()
    {
        $this->setServerEnv();
    }

    public function testInvoke(UnitTester $I)
    {
        $defaults = $this->testValuesDefaults();
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willImplement(ContainerInterface::class);
        $config = [
            'test_values' => [
                'isExpected' => false,
                'options' => [
                    'color' => 'blue',
                ],
                'trueOrFalse' => true,
            ],
        ];
        $prophecy->get('config')->willReturn($config);
        $container = $prophecy->reveal();

        $values = (new GatherConfigValues)($container, 'test_values', $defaults);

        $I->assertEquals($this->expectedConfig(), $values);
    }

    public function testInvokeWithDots(UnitTester $I)
    {
        $defaults = $this->testValuesDefaults();
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willImplement(ContainerInterface::class);
        $config = [
            'test.values' => [
                'isExpected' => false,
                'options' => [
                    'color' => 'blue',
                ],
                'trueOrFalse' => true,
            ],
        ];
        $prophecy->get('config')->willReturn($config);
        $container = $prophecy->reveal();

        $values = (new GatherConfigValues)($container, 'test.values', $defaults);

        $I->assertEquals($this->expectedConfig(), $values);
    }

    public function testInvokeWithNoDefault(UnitTester $I)
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willImplement(ContainerInterface::class);
        $config = [
            'testing' => [
                'isExpected' => false,
                'options' => [
                    'color' => 'blue',
                ],
                'trueOrFalse' => true,
            ],
        ];
        $prophecy->get('config')->willReturn($config);
        $container = $prophecy->reveal();

        $values = (new GatherConfigValues)($container, 'testing');

        $I->assertEquals($this->expectedConfigNoDefaults(), $values);
    }

    public function testInvokeWithNoDefaultNoConfig(UnitTester $I)
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willImplement(ContainerInterface::class);
        $config = [];
        $prophecy->get('config')->willReturn($config);
        $container = $prophecy->reveal();

        $values = (new GatherConfigValues)($container, 'testing');

        $I->assertEquals($this->expectedConfigNoDefaultsNoConfig(), $values);
    }

    private function expectedConfig(): array
    {
        return [
            'default'     => 'set',
            'data'        => [
                'alpha' => 'alpha',
                'beta'  => 'beta',
                'gamma' => 'gamma',
            ],
            'isExpected'  => false,
            'not'         => [
                'set' => [
                    'anywhere' => 'blah',
                ],
            ],
            'options'     => [
                'color' => 'blue',
                'size'  => 'large',
            ],
            'trueOrFalse' => false,
            'value'       => 42,
            'url'         => 'https://zestic.com',
        ];
    }

    private function expectedConfigNoDefaults(): array
    {
        return [
            'data'        => [
                'alpha' => 'alpha',
                'beta'  => 'beta',
                'gamma' => 'gamma',
            ],
            'isExpected'  => false,
            'not'         => [
                'set' => [
                    'anywhere' => 'blah',
                ],
            ],
            'options'     => [
                'color' => 'blue',
                'size'  => 'large',
            ],
            'trueOrFalse' => false,
            'value'       => 42,
            'url'         => 'https://zestic.com',
        ];
    }

    private function expectedConfigNoDefaultsNoConfig(): array
    {
        return [
            'data'        => [
                'alpha' => 'alpha',
                'beta'  => 'beta',
                'gamma' => 'gamma',
            ],
            'not'         => [
                'set' => [
                    'anywhere' => 'blah',
                ],
            ],
            'options'     => [
                'size' => 'large',
            ],
            'trueorfalse' => false,
            'value'       => 42,
            'url'         => 'https://zestic.com',
        ];
    }

    private function testValuesDefaults(): array
    {
        return [
            'default' => 'set',
            'options' => [
                'color' => null,
                'size' => null,
                'style' => null,
            ],
            'trueOrFalse' => true,
            'value' => null,
            'url' => null,
        ];
    }

    private function setServerEnv()
    {
        $_SERVER['TEST_VALUES_DATA_ALPHA'] = 'alpha';
        $_SERVER['TEST_VALUES_DATA_BETA'] = 'beta';
        $_SERVER['TEST_VALUES_DATA_GAMMA'] = 'gamma';
        $_SERVER['TEST_VALUES_NOT_SET_ANYWHERE'] = 'blah';
        $_SERVER['TEST_VALUES_OPTIONS_SIZE'] = 'large';
        $_SERVER['TEST_VALUES_TRUEORFALSE'] = false;
        $_SERVER['TEST_VALUES_VALUE'] = 42;
        $_SERVER['TEST_VALUES_URL'] = 'https://zestic.com';
        $_SERVER['TESTING_DATA_ALPHA'] = 'alpha';
        $_SERVER['TESTING_DATA_BETA'] = 'beta';
        $_SERVER['TESTING_DATA_GAMMA'] = 'gamma';
        $_SERVER['TESTING_NOT_SET_ANYWHERE'] = 'blah';
        $_SERVER['TESTING_OPTIONS_SIZE'] = 'large';
        $_SERVER['TESTING_TRUEORFALSE'] = false;
        $_SERVER['TESTING_VALUE'] = 42;
        $_SERVER['TESTING_URL'] = 'https://zestic.com';
    }
}
