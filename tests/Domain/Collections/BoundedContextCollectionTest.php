<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\ValueObjects\BoundedContext;

class BoundedContextCollectionTest extends TestCase
{
    public function testCount()
    {
        $collection = new BoundedContextCollection([
            $this->bc('a'),
            $this->bc('b'),
            $this->bc('c'),
        ]);

        $this->assertCount(3, $collection);

        $collection->add($this->bc('d'));
        $this->assertCount(4, $collection);

        $collection->add($this->bc('a'));
        $this->assertCount(4, $collection);

        $collection->add($this->bc('e'));
        $collection->add($this->bc('f'));
        $this->assertCount(6, $collection);

        $collection->add($this->bc('e'));
        $collection->add($this->bc('g'));
        $this->assertCount(7, $collection);

        $this->assertSame(7, iterator_count($collection));
    }

    private function bc(string $name): BoundedContext
    {
        return new BoundedContext($name);
    }
}
