<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects\CQS;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class QueryTest extends TestCase
{
    /**
     * @dataProvider providerTestNominal
     */
    public function testNominal(string $fqnAsString, string $name, string $classname)
    {
        $interpreter = new NamespaceInterpreter(0);
        $fqn = $interpreter->translate(new FullyQualifiedName($fqnAsString));

        $query = new Query($fqn);

        $this->assertSame($name, $query->name());
        $this->assertSame($classname, $query->classname());

        $this->assertTrue($query->equals(new Query($fqn)));

        $json = $query->jsonSerialize();
        $this->assertSame('BC', $json['bc']);

        $this->assertSame($fqnAsString, (string) $query);
    }

    public function providerTestNominal()
    {
        return [
            ['BC\\Application\\Queries\\X\\Y\\Z\\PonyQuery', 'X\\Y\\Z', 'PonyQuery'],
            ['BC\\Application\\Queries\\X\\UnicornQuery', 'X', 'UnicornQuery'],
            ['BC\\Application\\Queries\\PegasusQuery', '', 'PegasusQuery'],
        ];
    }

}
