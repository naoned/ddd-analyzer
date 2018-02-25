<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections;

use Niktux\DDD\Analyzer\Domain\Type;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class TypeCollection implements \IteratorAggregate, \Countable
{
    private
        $types;

    public function __construct(iterable $types = [])
    {
        $this->types = [];

        foreach($types as $type)
        {
            if($type instanceof Type)
            {
                $this->add($type);
            }
        }
    }

    public function add(Type $type): self
    {
        $this->types[(string) $type->fqn()] = $type;

        return $this;
    }

    public function has(FullyQualifiedName $fqn): bool
    {
        return isset($this->types[(string) $fqn]);
    }

    public function get(FullyQualifiedName $fqn): ?Type
    {
        if($this->has($fqn))
        {
            return $this->types[(string) $fqn];
        }

        return null;
    }

    public function contains(Type $target): bool
    {
        foreach($this->types as $type)
        {
            if($type->equals($target))
            {
                return true;
            }
        }

        return false;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->types);
    }

    public function count(): int
    {
        return count($this->types);
    }
}
