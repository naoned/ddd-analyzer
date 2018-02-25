<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects\CQS;

use Puzzle\Pieces\ConvertibleToString;
use Onyx\Domain\ValueObject;
use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;

abstract class AbstractCQSItem implements ValueObject, ConvertibleToString, \JsonSerializable
{
    private
        $fqn;

    public function __construct(InterpretedFQN $fqn)
    {
        $this->fqn = $fqn;
    }

    public function name(): string
    {
        $parts = explode('\\', $this->fqn->relativeName());
        $parts = array_slice($parts, 1);
        array_pop($parts);

        return implode('\\', $parts);
    }

    public function classname(): string
    {
        $parts = explode('\\', $this->fqn->relativeName());

        return array_pop($parts);
    }

    public function equals(self $item): bool
    {
        return $this->fqn->equals($item->fqn);
    }

    public function __toString(): string
    {
        return $this->fqn->__toString();
    }

    public function jsonSerialize(): array
    {
        return [
            'bc' => (string) $this->fqn->boundedContext(),
            'name' => $this->name(),
            'class' => $this->classname(),
        ];
    }
}
