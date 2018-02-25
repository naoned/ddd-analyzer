<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections\CQS;

use Niktux\DDD\Analyzer\Domain\ValueObjects\CQS\Query;

class QueryCollection implements \IteratorAggregate, \Countable
{
    private
        $queries;

    public function __construct(iterable $queries = [])
    {
        $this->queries = [];

        foreach($queries as $query)
        {
            if($query instanceof Query)
            {
                $this->add($query);
            }
        }
    }

    public function add(Query $query): self
    {
        $this->queries[$query->__toString()] = $query;

        return $this;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->queries);
    }

    public function count(): int
    {
        return count($this->queries);
    }
}
