<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\Type;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class TypeCollectionTest extends TestCase
{
    public function testCount()
    {
        $collection = new TypeCollection([
            $this->type('a'),
            $this->type('b'),
            $this->type('c'),
        ]);

        $this->assertCount(3, $collection);

        $collection->add($this->type('d'));
        $this->assertCount(4, $collection);

        $collection->add($this->type('a'));
        $this->assertCount(4, $collection);

        $collection->add($this->type('e'));
        $collection->add($this->type('f'));
        $this->assertCount(6, $collection);

        $collection->add($this->type('e'));
        $collection->add($this->type('g'));
        $this->assertCount(7, $collection);

        $this->assertSame(7, iterator_count($collection));
    }

    /**
     * @dataProvider providerTestHasAndContains
     */
    public function testHasAndContains(string $name, bool $expected)
    {
        $collection = new TypeCollection([
            $this->type('A'),
            $this->type('A\\B'),
            $this->type('A\\B\\C'),
            $this->type('X\\Y\\Z'),
        ]);

        $this->assertSame(
            $expected,
            $collection->has(new FullyQualifiedName($name))
        );

        $this->assertSame(
            $expected,
            $collection->contains($this->type($name))
        );
    }

    public function providerTestHasAndContains()
    {
        return [
            ['A', true],
            ['A\\B', true],
            ['A\\B\\C', true],
            ['X\\Y\\Z', true],

            ['B', false],
            ['C', false],
            ['B\\C', false],
            ['X\\Y', false],
        ];
    }

    public function testGet()
    {
        $collection = new TypeCollection([
            $this->type('A'),
            $this->type('A\\B'),
            $this->type('A\\B\\C'),
            $this->type('X\\Y\\Z'),
        ]);

        $this->assertNull($collection->get(new FullyQualifiedName('P\\Q\\R')));

        $this->assertTrue(
            $this->type('A\\B')->equals(
                $collection->get(new FullyQualifiedName('A\\B'))
        ));
    }

    private function type(string $name): Type
    {
        return new Type(new FullyQualifiedName($name));
    }
}
