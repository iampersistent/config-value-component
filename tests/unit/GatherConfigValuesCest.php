<?php
declare(strict_types=1);

namespace Tests\Unit;

use ConfigValue\GatherConfigValues;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use UnitTester;

class GatherConfigValuesCest
{
    public function testInvoke(UnitTester $I)
    {
        $_SERVER['TESTING_NOT_SET_ANYWHERE'] = 'blah';
        $_SERVER['TESTING_OPTIONS_SIZE'] = 'large';
        $_SERVER['TESTING_TRUE_OR_FALSE'] = false;
        $_SERVER['TESTING_VALUE'] = 42;
        $_SERVER['TESTING_URL'] = 'https://xaddax.com';

        $defaults = [
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

        $values = (new GatherConfigValues)($container, 'testing', $defaults);

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
            'default' => 'set',
            'isExpected' => false,
            'notSetAnywhere' => 'blah',
            'options' => [
                'color' => 'blue',
                'size' => 'large',
            ],
            'trueOrFalse' => false,
            'value' => 42,
            'url' => 'https://xaddax.com',
        ];
    }

    private function expectedConfigNoDefaults(): array
    {
        return [
            'isExpected' => false,
            'notSetAnywhere' => 'blah',
            'options' => [
                'color' => 'blue',
                'size' => 'large',
            ],
            'trueOrFalse' => false,
            'value' => 42,
            'url' => 'https://xaddax.com',
        ];
    }

    private function expectedConfigNoDefaultsNoConfig(): array
    {
        return [
            'notSetAnywhere' => 'blah',
            'optionsSize' => 'large',
            'trueOrFalse' => false,
            'value' => 42,
            'url' => 'https://xaddax.com',
        ];
    }
}
