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
        $allowed = ['preAnalyze', 'analyze'];

        if(! in_array($value, $allowed))
        {
            throw new \LogicException("Invalid traverse mode : $value");
        }

        $this->value = $value;
    }

    public static function preAnalyze(): self
    {
        return new self('preAnalyze');
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