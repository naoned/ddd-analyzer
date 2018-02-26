<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Puzzle\Pieces\ConvertibleToString;
use Niktux\DDD\Analyzer\Domain\ValueObjects\ObjectType;

class Type implements ConvertibleToString, \JsonSerializable
{
    private
        $objectType,
        $fqn,
        $others;

    public
        $resolved;

    public function __construct(FullyQualifiedName $fqn, ?ObjectType $type = null)
    {
        $this->objectType = $type;
        $this->fqn = $fqn;
        $this->others = [];
        $this->resolved = false;
    }

    public function objectType(): ?ObjectType
    {
        return $this->objectType;
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
        if($target->equals($this))
        {
            return true;
        }

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
            $others[] = (string) $other->fqn();
        }

        $json = [
            'name' => (string) $this->fqn,
        ];

        if($this->objectType !== null)
        {
            $json['type'] = (string) $this->objectType;
        }

        $json['instanceof'] = $others;

        return $json;
    }
}
