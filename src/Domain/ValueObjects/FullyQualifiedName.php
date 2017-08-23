<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use Puzzle\Pieces\ConvertibleToString;
use Onyx\Domain\ValueObject;

final class FullyQualifiedName implements ValueObject, ConvertibleToString
{
    private
        $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function value(): string
    {
        return $this->name;
    }

    public function equals(self $name): bool
    {
        return $this->name === $name->value();
    }

    public function getParts(int $slice = 0): array
    {
        $parts = explode('\\', $this->name);

        return array_slice($parts, $slice);
    }

    public function getLast(): string
    {
        $parts = $this->getParts();

        return array_pop($parts);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
