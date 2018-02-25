<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections;

use Niktux\DDD\Analyzer\Domain\ValueObjects\BoundedContext;

class BoundedContextCollection implements \IteratorAggregate, \Countable
{
    private
        $contexts;

    public function __construct(iterable $contexts = [])
    {
        $this->contexts = [];

        foreach($contexts as $context)
        {
            if($context instanceof BoundedContext)
            {
                $this->add($context);
            }
        }
    }

    public function add(BoundedContext $context): self
    {
        $this->contexts[$context->value()] = $context;

        return $this;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->contexts);
    }

    public function count(): int
    {
        return count($this->contexts);
    }
}
