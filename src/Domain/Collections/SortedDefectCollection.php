<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections;

class SortedDefectCollection implements DefectRelatedCollection
{
    private
        $collections;

    public function __construct(iterable $collections = [])
    {
        $this->collections = [];

        foreach($collections as $collection)
        {
            if($collection instanceof DefectRelatedCollection)
            {
                $this->add($collection);
            }
        }
    }

    public function add(string $id, DefectRelatedCollection $collection): self
    {
        $this->collections[$id] = $collection;

        return $this;
    }

    public function exists(string $id): bool
    {
        return isset($this->collections[$id]);
    }

    public function addIn(string $id, $value): self
    {
        if($this->exists($id))
        {
            $this->collections[$id]->add($value); // FIXME not in interface :-(
        }

        return $this;
    }

    public function addIfNotExists(string $id, DefectRelatedCollection $collection): self
    {
        if(! $this->exists($id))
        {
            $this->add($id, $collection);
        }

        return $this;
    }

    public function get(string $id): ?DefectRelatedCollection
    {
        if($this->exists($id))
        {
            return $this->collections[$id];
        }

        return null;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->collections);
    }

    public function count(): int
    {
        return count($this->collections);
    }
}
