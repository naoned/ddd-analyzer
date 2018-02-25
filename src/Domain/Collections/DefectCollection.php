<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections;

use Niktux\DDD\Analyzer\Domain\ContextualizedDefect;

class DefectCollection implements DefectRelatedCollection
{
    private
        $defects;

    public function __construct(iterable $defects = [])
    {
        $this->defects = [];

        foreach($defects as $defect)
        {
            if($defect instanceof ContextualizedDefect)
            {
                $this->add($defect);
            }
        }
    }

    public function add(ContextualizedDefect $defect): self
    {
        $this->defects[] = $defect;

        return $this;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->defects);
    }

    public function count(): int
    {
        return count($this->defects);
    }
}
