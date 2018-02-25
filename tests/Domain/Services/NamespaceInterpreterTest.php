<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Services;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class NamespaceInterpreterTest extends TestCase
{
    /**
     * @dataProvider providerTestCanTranslate
     */
    public function testCanTranslate(string $namespace, bool $expected)
    {
        $interpreter = new NamespaceInterpreter(2);

        $this->assertSame(
            $expected,
            $interpreter->canTranslate(new FullyQualifiedName($namespace))
        );
    }

    public function providerTestCanTranslate()
    {
        return [
            ['A', false],
            ['A\\B', false],
            ['A\\B\\BC', false],
            ['A\\B\\BC\\Domain', false],
            ['A\\B\\BC\\Domain\\X', true],
            ['A\\B\\BC\\Domain\\X\\Y', true],
            ['A\\B\\BC\\Domain\\X\\Y\\Z', true],

            ['A\\B\\BC\\NotAValidLayer\\X\\Y\\Z', false],

            ['A\\B\\Controllers\\Domain\\X\\Y\\Z', false],
            ['A\\B\\Domain\\Domain\\X\\Y\\Z', false],
            ['A\\B\\Console\\Domain\\X\\Y\\Z', false],
        ];
    }


    /**
     * @dataProvider providerTestTranslate
     */
    public function testTranslate(string $name, string $expected)
    {
        $interpreter = new NamespaceInterpreter(2);
        $name = $interpreter->translate(new FullyQualifiedName($name));

        $this->assertSame($expected, $name->__toString());
    }

    public function providerTestTranslate()
    {
        return [
            ['A\\B\\BC\\Domain\\X', 'BC\\Domain\\X'],
            ['A\\B\\BC\\Application\\X', 'BC\\Application\\X'],
            ['A\\B\\BC\\Infrastructure\\X\\Y', 'BC\\Infrastructure\\X\\Y'],
            ['A\\B\\Other\\Domain\\X\\Y\\Z', 'Other\\Domain\\X\\Y\\Z'],
            ['A\\B\\Application\\Application\\X\\Y\\Z', 'Application\\Application\\X\\Y\\Z'],
        ];
    }

    /**
     * @dataProvider providerTestTranslateWrong
     * @expectedException \LogicException
     */
    public function testTranslateWrong(string $namespace)
    {
        $interpreter = new NamespaceInterpreter(2);
        $interpreter->translate(new FullyQualifiedName($namespace));
    }

    public function providerTestTranslateWrong()
    {
        return [
            ['A'],
            ['A\\B'],
            ['A\\B\\Domain\\X\\Y'],
            ['A\\B\\BC'],
            ['A\\B\\BC\\domain'],
            ['A\\B\\BC\\Persistence\\X'],
            ['A\\B\\BC\\App\\X\\Y'],
            ['A\\B\\BC\\Infra\\X\\Y\\Z'],
        ];
    }
}
