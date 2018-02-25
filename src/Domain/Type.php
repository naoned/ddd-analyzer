<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Puzzle\Pieces\ConvertibleToString;

class Type implements ConvertibleToString, \JsonSerializable
{
    private
        $fqn,
        $others;

    public
        $resolved;

    public function __construct(FullyQualifiedName $fqn)
    {
        $this->fqn = $fqn;
        $this->others = [];
        $this->resolved = false;
    }

    public function fqn(): FullyQualifiedName
    {
        return $this->fqn;
    }

    public function addType(Type $type): void
    {
        $this->others[(string) $type] = $type;
    }

    public function isA(Type $target): bool
    {
        foreach($this->others as $type)
        {
            if($type->equals($target))
            {
                return true;
            }
        }

        return false;
    }

    public function others(): array
    {
        return $this->others;
    }

    public function __toString(): string
    {
        return (string) $this->fqn;
    }

    public function equals(self $type): bool
    {
        return $this->fqn->equals($type->fqn);
    }

    public function jsonSerialize(): array
    {
        $others = [];

        foreach($this->others as $other)
        {
            $fqn = $other->fqn();

            if(! $fqn->equals($this->fqn))
            {
                $others[] = (string) $other->fqn();
            }
        }

        return [
            'name' => (string) $this->fqn,
            'instanceof' => $others,
        ];
    }
}
