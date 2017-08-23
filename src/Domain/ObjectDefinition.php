<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Niktux\DDD\Analyzer\Domain\ValueObjects\ObjectType;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

final class ObjectDefinition
{
    private
        $namespace,
        $name,
        $type;

    public function __construct(FullyQualifiedName $namespace, $name, ?ObjectType $type = null)
    {
        if($type === null)
        {
            $type = ObjectType::class();
        }

        $this->namespace = $namespace;
        $this->name = $name;
        $this->type = $type;
    }

    public function namespace(): FullyQualifiedName
    {
        return $this->namespace;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function fullname(): string
    {
        return empty($namespace) ? $name : $namespace->value() . "\\$name";
    }

    public function type(): ObjectType
    {
        return $this->type;
    }
}
