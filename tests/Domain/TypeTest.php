<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class TypeTest extends TestCase
{
    public function test()
    {
        $foo = $this->type('Foo');
        $abstractFoo = $this->type('AbstractFoo');
        $bar = $this->type('Bar');
        $baz = $this->type('Baz');

        $foo->addType($abstractFoo);
        $foo->addType($bar);

        $this->assertTrue($foo->isA($foo));

        $this->assertTrue($foo->isA($abstractFoo));
        $this->assertTrue($foo->isA($bar));

        $this->assertFalse($foo->isA($baz));
        $this->assertFalse($bar->isA($foo));

        $this->assertFalse($bar->isA($abstractFoo));
        $bar->addType($abstractFoo);
        $this->assertTrue($bar->isA($abstractFoo));

        $this->assertCount(2, $foo->others());

        $this->assertJsonStringEqualsJsonString(
            '{"name": "Foo", "instanceof": ["AbstractFoo", "Bar"]}',
            json_encode($foo->jsonSerialize())
        );
    }

    private function type(string $name): Type
    {
        return new Type(new FullyQualifiedName($name));
    }
}
