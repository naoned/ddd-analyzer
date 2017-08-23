<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use Puzzle\Pieces\ConvertibleToString;
use Onyx\Domain\ValueObject;

final class BoundedContext implements ValueObject, ConvertibleToString
{
    private
        $context;

    public function __construct(string $context)
    {
        $this->context = $context;
    }

    public function value(): string
    {
        return $this->context;
    }

    public function equals(self $context): bool
    {
        return $this->context === $context->value();
    }

    public function __toString(): string
    {
        return $this->context;
    }
}
