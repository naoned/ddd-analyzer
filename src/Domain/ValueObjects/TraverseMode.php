<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use Puzzle\Pieces\ConvertibleToString;
use Onyx\Domain\ValueObject;

final class TraverseMode implements ValueObject, ConvertibleToString
{
    private
        $value;

    private function __construct(string $value)
    {
        $allowed = ['complete', 'raw_collect', 'infer', 'collect', 'analyze'];

        if(! in_array($value, $allowed))
        {
            throw new \LogicException("Invalid traverse mode : $value");
        }

        $this->value = $value;
    }

    public static function complete(): self
    {
        return new self('complete');
    }

    public static function rawCollect(): self
    {
        return new self('raw_collect');
    }

    public static function infer(): self
    {
        return new self('infer');
    }

    public static function collect(): self
    {
        return new self('collect');
    }

    public static function analyze(): self
    {
        return new self('analyze');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $traverseMode): bool
    {
        return $this->value === $traverseMode->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}