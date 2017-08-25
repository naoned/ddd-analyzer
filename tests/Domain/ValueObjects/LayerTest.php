<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;

class LayerTest extends TestCase
{
    /**
     * @dataProvider providerTestIsDeeperThan
     */
    public function testIsDeeperThan(Layer $is, Layer $than, bool $expected)
    {
        $this->assertSame($expected, $is->isDeeperThan($than));
    }

    public function providerTestIsDeeperThan()
    {
        return [
            [Layer::domain(), Layer::domain(), false],
            [Layer::domain(), Layer::application(), true],
            [Layer::domain(), Layer::infrastructure(), true],

            [Layer::application(), Layer::domain(), false],
            [Layer::application(), Layer::application(), false],
            [Layer::application(), Layer::infrastructure(), true],

            [Layer::infrastructure(), Layer::domain(), false],
            [Layer::infrastructure(), Layer::application(), false],
            [Layer::infrastructure(), Layer::infrastructure(), false],
        ];
    }

    /**
     * @dataProvider providerTestIsValid
     */
    public function testIsValid(string $value, bool $expected)
    {
        $this->assertSame($expected, Layer::isValid($value));
    }

    public function providerTestIsValid()
    {
        return [
            ['Domain', true],
            ['Application', true],
            ['Infrastructure', true],

            ['domain', false],
            ['application', false],
            ['infrastructure', false],

            ['pony', false],
            ['Provider', false],
            ['Controllers', false],
        ];
    }

    public function testIsValidFromNamedContructors()
    {
        $layers = [
            Layer::domain(),
            Layer::application(),
            Layer::infrastructure(),
        ];

        foreach($layers as $layer)
        {
            $this->assertTrue(Layer::isValid($layer->value()));
        }
    }

    /**
     * @dataProvider providerTestEquals
     */
    public function testEquals(Layer $layer, Layer $other, bool $expected)
    {
        $this->assertSame($expected, $layer->equals($other));
    }

    public function providerTestEquals()
    {
        return [
            [Layer::domain(), Layer::domain(), true],
            [Layer::domain(), Layer::application(), false],
            [Layer::domain(), Layer::infrastructure(), false],

            [Layer::application(), Layer::domain(), false],
            [Layer::application(), Layer::application(), true],
            [Layer::application(), Layer::infrastructure(), false],

            [Layer::infrastructure(), Layer::domain(), false],
            [Layer::infrastructure(), Layer::application(), false],
            [Layer::infrastructure(), Layer::infrastructure(), true],
        ];
    }

    /**
     * @expectedException \LogicException
     */
    public function testInvalidValue()
    {
        $value = 'Pony';

        $this->assertFalse(Layer::isValid($value));

        new Layer($value);
    }
}
