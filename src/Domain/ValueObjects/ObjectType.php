<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects;

use Puzzle\Pieces\ConvertibleToString;
use Onyx\Domain\ValueObject;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

final class ObjectType implements ValueObject, ConvertibleToString
{
    private
        $value;

    private function __construct(string $value)
    {
        $allowed = ['class', 'interface', 'trait'];

        if(! in_array($value, $allowed))
        {
            throw new \LogicException("Invalid object type : $value");
        }

        $this->value = $value;
    }

    public static function class(): self
    {
        return new self('class');
    }

    public static function interface(): self
    {
        return new self('interface');
    }

    public static function trait(): self
    {
        return new self('trait');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $objectType): bool
    {
        return $this->value === $objectType->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromNode(ClassLike $node): self
    {
        if($node instanceof Class_)
        {
            return self::class();
        }
        if($node instanceof Interface_)
        {
            return self::interface();
        }
        if($node instanceof Trait_)
        {
            return self::trait();
        }

        throw new \RuntimeException("Undefined object type : " . get_class($node));
    }
}