<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections\CQS;

use PHPUnit\Framework\TestCase;
use Niktux\DDD\Analyzer\Domain\ValueObjects\CQS\Command;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class CommandCollectionTest extends TestCase
{
    public function testCount()
    {
        $collection = new CommandCollection([
            $this->item('a'),
            $this->item('b'),
            $this->item('c'),
        ]);

        $this->assertCount(3, $collection);

        $collection->add($this->item('d'));
        $this->assertCount(4, $collection);

        $collection->add($this->item('a'));
        $this->assertCount(4, $collection);

        $collection->add($this->item('e'));
        $collection->add($this->item('f'));
        $this->assertCount(6, $collection);

        $collection->add($this->item('e'));
        $collection->add($this->item('g'));
        $this->assertCount(7, $collection);

        $this->assertSame(7, iterator_count($collection));
    }

    private function item(string $name): Command
    {
        $name = 'BC\\Application\\Commands\\' . $name;

        $interpreter = new NamespaceInterpreter(0);
        $fqn = $interpreter->translate(
            new FullyQualifiedName($name)
        );

        return new Command($fqn);
    }
}
