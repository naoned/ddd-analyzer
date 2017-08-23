<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use Puzzle\Pieces\ConvertibleToString;
use Onyx\Domain\ValueObject;

final class Layer implements ValueObject, ConvertibleToString
{
    private
        $depth,
        $value;

    public function __construct(string $value)
    {
        if(self::isValid($value) === false)
        {
            throw new \LogicException("Invalid layer : $value");
        }

        $this->value = $value;
        $this->depth = array_search($value, self::validLayers());
    }

    private static function validLayers(): array
    {
        return ['Domain', 'Application', 'Infrastructure'];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::validLayers());
    }

    public static function domain(): self
    {
        return new self('Domain');
    }

    public static function application(): self
    {
        return new self('Application');
    }

    public static function infrastructure(): self
    {
        return new self('Infrastructure');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $layer): bool
    {
        return $this->value === $layer->value();
    }

    public function isDeeperThan(self $layer): bool
    {
        return $this->depth < $layer->depth;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
