<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects\CQS;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class CommandTest extends TestCase
{
    /**
     * @dataProvider providerTestNominal
     */
    public function testNominal(string $fqnAsString, string $name, string $classname)
    {
        $interpreter = new NamespaceInterpreter(0);
        $fqn = $interpreter->translate(new FullyQualifiedName($fqnAsString));

        $command = new Command($fqn);

        $this->assertSame($name, $command->name());
        $this->assertSame($classname, $command->classname());

        $this->assertTrue($command->equals(new Command($fqn)));

        $json = $command->jsonSerialize();
        $this->assertSame('BC', $json['bc']);

        $this->assertSame($fqnAsString, (string) $command);
    }

    public function providerTestNominal()
    {
        return [
            ['BC\\Application\\Commands\\X\\Y\\Z\\PonyCommand', 'X\\Y\\Z', 'PonyCommand'],
            ['BC\\Application\\Commands\\X\\UnicornCommand', 'X', 'UnicornCommand'],
            ['BC\\Application\\Commands\\PegasusCommand', '', 'PegasusCommand'],
        ];
    }

}
