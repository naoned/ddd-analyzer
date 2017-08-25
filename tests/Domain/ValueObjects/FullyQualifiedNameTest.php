<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;

class FullyQualifiedNameTest extends TestCase
{
    /**
     * @dataProvider providerTestGetLast
     */
    public function testGetLast(string $name, string $expected)
    {
        $fqn = new FullyQualifiedName($name);

        $this->assertSame($expected, $fqn->getLast());
    }

    public function providerTestGetLast()
    {
        return [
            ["Niktux\DDD\Analyzer\Domain\ValueObjects", "ValueObjects"],
        ];
    }

}
