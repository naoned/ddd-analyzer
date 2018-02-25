<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\ContextualizedDefect;
use Niktux\DDD\Analyzer\Domain\Defects\AnonymousClass;
use PhpParser\Node\Name;

class DefectCollectionTest extends TestCase
{
    public function testCount()
    {
        $collection = new DefectCollection([
            $this->item('a'),
            $this->item('b'),
            $this->item('c'),
        ]);

        $this->assertCount(3, $collection);

        $collection->add($this->item('d'));
        $this->assertCount(4, $collection);

        $collection->add($this->item('a'));
        $this->assertCount(5, $collection);

        $collection->add($this->item('e'));
        $collection->add($this->item('f'));
        $this->assertCount(7, $collection);

        $collection->add($this->item('e'));
        $collection->add($this->item('g'));
        $this->assertCount(9, $collection);

        $this->assertSame(9, iterator_count($collection));
    }

    private function item(string $name): ContextualizedDefect
    {
        return new ContextualizedDefect(
            new AnonymousClass(new Name($name)),
            '/path/to/file'
        );
    }
}
