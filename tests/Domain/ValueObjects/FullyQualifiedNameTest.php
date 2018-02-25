<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use PhpParser\Node\Name\FullyQualified;

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
            ["Niktux\\DDD\\Analyzer\\Domain\\ValueObjects", "ValueObjects"],
            ["Niktux\\DDD\\Analyzer\\Domain", "Domain"],
            ["Niktux", "Niktux"],
        ];
    }

    public function testEquals()
    {
        $fqn1a = new FullyQualifiedName("A\\B\\C");
        $fqn1b = new FullyQualifiedName("A\\B\\C");
        $fqn2 = new FullyQualifiedName("A\\B");

        $this->assertTrue($fqn1a->equals($fqn1b));
        $this->assertTrue($fqn1b->equals($fqn1a));

        $this->assertTrue($fqn1a->equals($fqn1a));
        $this->assertTrue($fqn1b->equals($fqn1b));
        $this->assertTrue($fqn2->equals($fqn2));

        $this->assertfalse($fqn1a->equals($fqn2));
        $this->assertfalse($fqn2->equals($fqn1a));

        $this->assertfalse($fqn1b->equals($fqn2));
        $this->assertfalse($fqn2->equals($fqn1b));
    }

    public function testFromPhpParserFQN()
    {
        $fq = new FullyQualified("A\\B");
        $expected = new FullyQualifiedName("A\\B");

        $this->assertTrue($expected->equals(FullyQualifiedName::fromPhpParserFQN($fq)));
    }

    /**
     * @dataProvider providerTestConcat
     */
    public function testConcat(string $name1, string $name2, string $expected)
    {
        $f1 = new FullyQualifiedName($name1);
        $fqn = $f1->concat($name2);

        $this->assertNotSame($f1, $fqn, "after concat, we must have a new instance");

        $expected = new FullyQualifiedName($expected);
        $this->assertTrue($expected->equals($fqn));
    }

    public function providerTestConcat()
    {
        return [
            ['A', 'C', 'A\\C'],
            ['A\\B', 'C', 'A\\B\\C'],

            ['A', 'C\\D', 'A\\C\\D'],
            ['A\\B', 'C\\D', 'A\\B\\C\\D'],
        ];
    }

}
