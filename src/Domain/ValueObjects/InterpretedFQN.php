<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use Puzzle\Pieces\ConvertibleToString;
use Onyx\Domain\ValueObject;

final class InterpretedFQN implements ValueObject, ConvertibleToString
{
    private
        $fqn,
        $boundedContext,
        $layer,
        $relativeName;

    public function __construct(FullyQualifiedName $fqn, BoundedContext $boundedContext, Layer $layer, string $relativeName)
    {
        $this->fqn = $fqn;
        $this->boundedContext = $boundedContext;
        $this->layer = $layer;
        $this->relativeName = $relativeName;
    }

    public function fqn(): FullyQualifiedName
    {
        return $this->fqn;
    }

    public function boundedContext(): BoundedContext
    {
        return $this->boundedContext;
    }

    public function layer(): Layer
    {
        return $this->layer;
    }

    public function relativeName(): string
    {
        return $this->relativeName;
    }

    public function equals(self $fqn): bool
    {
        return $this->fqn->equals($fqn->fqn());
    }

    public function concat(string $name): self
    {
        $fqn = $this->fqn->concat($name);
        $relativeName = $this->relativeName . "\\" . $name;

        return new self($fqn, $this->boundedContext, $this->layer, $relativeName);
    }

    public function __toString(): string
    {
        return sprintf('%s\\%s\\%s', $this->boundedContext, $this->layer, $this->relativeName);
    }
}
